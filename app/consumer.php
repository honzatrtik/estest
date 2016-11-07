<?php

/** @var Application $app */

use Bunny\Client;
use DI\Bridge\Silex\Application;
use EsTest\Event\Repository\EventRepository;
use EsTest\Projector\ProjectorSubscriber;
use EsTest\Projector\RethinkProjector;
use EsTest\Property\PropertyRepositoryInterface;

$app = require_once __DIR__ . '/bootstrap.php';
$di = $app->getPhpDi();

/** @var ProjectorSubscriber $projectorSubsciber */
$projectorSubsciber = new ProjectorSubscriber(
	'test',
	$di->get(Client::class),
	new RethinkProjector($di->get(\r\Connection::class)),
	$di->get(EventRepository::class),
	$di->get(PropertyRepositoryInterface::class)
);
$projectorSubsciber->reset();
$projectorSubsciber->subscribe();