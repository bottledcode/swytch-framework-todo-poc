<?php

use Bottledcode\SwytchFramework\Router\MagicRouter;
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
	Serializer::class => create(Serializer::class)
		->constructor([
			get(ArrayDenormalizer::class),
			get(ObjectNormalizer::class),
			get(DateTimeNormalizer::class)
		]),
]);
$container = $container->build();

$router = new MagicRouter($container, \Bottledcode\SwytchFrameworkTodo\Components\Index::class);
echo $router->go();
