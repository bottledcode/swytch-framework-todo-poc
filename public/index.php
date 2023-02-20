<?php

use Bottledcode\SwytchFramework\Router\Exceptions\InvalidRequest;
use Bottledcode\SwytchFramework\Router\MagicRouter;
use Bottledcode\SwytchFramework\Template\Interfaces\StateProviderInterface;
use Bottledcode\SwytchFramework\Template\ReferenceImplementation\ValidatedState;
use DI\ContainerBuilder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use function DI\create;
use function DI\get;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/attributes.php';

$container = new ContainerBuilder();
$container->addDefinitions([
	'state_secret' => 'alsfjowieirqr87y34875y3u4hrfjeaogfjeriu',
	StateProviderInterface::class => create(ValidatedState::class)
		->constructor(get('state_secret'), get(Serializer::class)),
	Serializer::class => create(Serializer::class)
		->constructor([
			get(ArrayDenormalizer::class),
			get(ObjectNormalizer::class),
			get(DateTimeNormalizer::class),
		]),
]);
$container = $container->build();

$router = new MagicRouter($container, \Bottledcode\SwytchFrameworkTodo\Components\Index::class);
try {
	$result = $router->go();
} catch (InvalidRequest $e) {
	http_response_code(400);
	echo $e->getMessage();
	die();
}
if ($result !== null) {
	echo $result;
	die();
}
http_response_code(404);
echo 'Not found';
