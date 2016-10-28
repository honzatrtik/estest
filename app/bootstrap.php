<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DI\Bridge\Silex\Application;
use Doctrine\Common\Cache\ArrayCache;
use Symfony\Component\HttpFoundation\Request;

define('ESTEST_ENV', getenv('ESTEST_ENV') ?: 'dev');

$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->setDefinitionCache(new ArrayCache());
$containerBuilder->addDefinitions(__DIR__ . '/config.' . ESTEST_ENV . '.php');
$containerBuilder->addDefinitions(__DIR__ . '/services.php');

$app = new Application($containerBuilder);
$app['debug'] = ESTEST_ENV === 'dev';

$app->before(function (Request $request) {
	if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
		$data = json_decode($request->getContent(), true);
		$request->request->replace(is_array($data) ? $data : []);
	}
});

return $app;

