<?php

use Bottledcode\SwytchFramework\CacheControl\Builder;
use Bottledcode\SwytchFramework\CacheControl\Queue;
use Bottledcode\SwytchFramework\Logging\StdOutputLogger;
use Bottledcode\SwytchFramework\Router\Exceptions\InvalidRequest;
use Bottledcode\SwytchFramework\Router\Exceptions\NotAuthorized;
use Bottledcode\SwytchFramework\Router\MagicRouter;
use Bottledcode\SwytchFramework\Template\Interfaces\AuthenticationServiceInterface;
use Bottledcode\SwytchFramework\Template\Interfaces\StateProviderInterface;
use Bottledcode\SwytchFramework\Template\ReferenceImplementation\ValidatedState;
use Bottledcode\SwytchFrameworkTodo\Language;
use DI\ContainerBuilder;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use r\Connection;
use r\ConnectionOptions;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use function DI\create;
use function DI\get;

$requestStart = microtime(true);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/attributes.php';

$container = new ContainerBuilder();
$container->addDefinitions([
	StateProviderInterface::class => fn(Serializer $serializer) => new ValidatedState(
		getenv('STATE_SECRET'),
		$serializer
	),
	Serializer::class => create(Serializer::class)
		->constructor([
			get(ArrayDenormalizer::class),

			get(BackedEnumNormalizer::class),
			//get(DateTimeNormalizer::class),
			get(ObjectNormalizer::class),
		]),
	Connection::class => fn() => new Connection(
		new ConnectionOptions(
			host: getenv('RETHINKDB_HOST'),
			db: getenv('RETHINKDB_DATABASE'),
			user: getenv('RETHINKDB_USER'),
			password: getenv('RETHINKDB_PASSWORD')
		)
	),
	LoggerInterface::class => static function (ContainerInterface $container) {
		$logger = new Logger('swytch');
		$handler = $container->get(StdOutputLogger::class);
		$handler->setFormatter(new LogstashFormatter('swytch-auth'));
		$logger->pushHandler($handler);
		$logger->pushProcessor($container->get(MemoryUsageProcessor::class));
		$logger->pushProcessor($container->get(MemoryPeakUsageProcessor::class));
		$logger->pushProcessor($container->get(WebProcessor::class));
		return $logger;
	},
]);
if (!function_exists('xdebug_break')) {
	$container->enableCompilation('/tmp');
}
$container = $container->build();

set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($container) {
	if (!(error_reporting() & $errno)) {
		// This error code is not included in error_reporting
		$container->get(LoggerInterface::class)->notice('Error suppressed', compact('errno', 'errstr', 'errfile', 'errline'));
		return;
	}
	http_response_code(500);
	$container->get(LoggerInterface::class)->critical('Fatal error', compact('errno', 'errstr', 'errfile', 'errline'));
	echo "Internal server error";
	die();
});

header('Vary: Accept-Language, Accept-Encoding, Accept');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

$router = new MagicRouter($container, \Bottledcode\SwytchFrameworkTodo\Components\Index::class);
try {
	$language = new Language($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', ['en', 'nl']);
	$container->set(Language::class, $language);
	$language->loadLanguage();
	header('Content-Language: ' . $language->currentLanguage);
	$result = $router->go();
	$caching = $container->get(Queue::class);
	usort($caching->queue, fn(Builder $a, Builder $b) => $a->compareScore($b));
	$cache = reset($caching->queue);
	if (!empty($cache) && method_exists($cache, 'render')) {
		header('Cache-Rendered: ' . $cache->tag);
		$cache->render($etag = md5($result));
		if ($cache->etagRequired && ($_SERVER['HTTP_IF_NONE_MATCH'] ?? null) === $etag) {
			http_response_code(304);
			header('Server-Timing: proc;dur=' . (microtime(true) - $requestStart));
			die();
		}
	}
} catch (InvalidRequest $e) {
	http_response_code(400);
	header('Server-Timing: proc;dur=' . (microtime(true) - $requestStart));
	echo $e->getMessage();
	die();
} catch (NotAuthorized) {
	http_response_code(401);
	echo "Not authorized";
	die();
}
if ($result !== null) {
	header('Server-Timing: proc;dur=' . (microtime(true) - $requestStart));
	$responseMs = number_format(
		round(
			(microtime(true) - $requestStart) * 1000,
			2
		),
		2
	);
	$newResult = str_replace(
		'TIMING_PLACEHOLDER',
		'<span id="timing" class="timing">' . $responseMs . ' milliseconds</span></html>',
		$result
	);
	if (($_SERVER['HTTP_HX_REQUEST'] ?? false) && $newResult === $result) {
		$newResult = '<span hx-swap-oob="true" id="timing" class="timing">' . $responseMs . ' milliseconds</span>' . $newResult;
	}
	echo $newResult;
	die();
}
header('Server-Timing: proc;dur=' . (microtime(true) - $requestStart));
http_response_code(404);
echo 'Not found';
// todo: display a 404?
