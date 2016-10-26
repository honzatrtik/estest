<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DI\Bridge\Silex\Application;
use Doctrine\Common\Cache\ArrayCache;
use EsTest\Event\Repository\EventRepositoryInterface;
use EsTest\Event\Repository\FileEventRepository;
use Symfony\Component\HttpFoundation\Request;

define('ESTEST_ENV', getenv('ESTEST_ENV') ?: 'dev');

$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->setDefinitionCache(new ArrayCache());
$containerBuilder->addDefinitions(__DIR__ . '/config.' . ESTEST_ENV . '.php');
$containerBuilder->addDefinitions([
	'file_event_repository.file_path' => __DIR__ . '/../temp/repository.txt',
	EventRepositoryInterface::class => DI\object(FileEventRepository::class)
		->constructorParameter('filePath', DI\get('file_event_repository.file_path')),
]);

$app = new Application($containerBuilder);
$app['debug'] = true;

$app->before(function (Request $request) {
	if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
		$data = json_decode($request->getContent(), true);
		$request->request->replace(is_array($data) ? $data : []);
	}
});

return $app;

