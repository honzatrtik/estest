<?php

use DI\Bridge\Silex\Application;
use EsTest\AggregateId;
use EsTest\BoardAggregate;
use EsTest\Event\DomainEvent;
use EsTest\Event\Persister\EventPersisterInterface;
use EsTest\Event\Publisher\EventPublisherInterface;
use EsTest\Event\Repository\EventRepository;
use EsTest\Player\PlayerToken;
use EsTest\Player\PlayerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @var Application $app */
$app = require_once __DIR__ . '/bootstrap.php';

$app->get('/events', function(Application $app, EventRepository $repository) {
	$data = $repository->fetchList()->map(function(DomainEvent $event) {
		return [
			'aggregateId' => (string) $event->getAggregateId(),
			'event' => $event->getEventName(),
			'created' => $event->getCreated()->format('Y-m-d H:i:s'),
		];
	})->toArray();
	return $app->json($data);
});

$app->get('/uuid', function(Application $app,\Doctrine\DBAL\Connection $c) {
	return $app->json((string) AggregateId::create());
});

$app->post('/game/create/{uuid}', function(
	$uuid,
	Request $request,
	Application $app,
	EventPersisterInterface $persister,
	EventPublisherInterface $publisher,
	EventRepository $repository
) {
	try {
		$boardId = AggregateId::createFromString($uuid);
		$board = BoardAggregate::loadFromHistory($boardId, $repository->fetchListByAggregateId($boardId));
		$board->create($boardId);
		$events = $board->getNotPersistedEventList();
		$persister->persistList($events);
		foreach($events as $event) {
			$publisher->publish($event);
		}
		return new Response('', 201);
	}
	catch (RuntimeException $e) {
		return $app->abort(400, $e->getMessage());
	}
});

$app->post('/game/join/{uuid}', function(
	$uuid,
	Request $request,
	Application $app,
	EventPersisterInterface $persister,
	EventPublisherInterface $eventPublisher,
	EventRepository $repository
) {
	$boardId = AggregateId::createFromString($uuid);
	$eventList = $repository->fetchListByAggregateId($boardId);
	if (!$eventList) {
		return $app->abort(404, 'Board not found');
	}
	if (!($token = $request->request->get('token'))) {
		return $app->abort(400, 'Missing token');
	}
	if (!($type = $request->request->get('type'))) {
		return $app->abort(400, 'Missing type');
	}
	if (!($name = $request->request->get('name'))) {
		return $app->abort(400, 'Missing name');
	}

	try {
		$board = BoardAggregate::loadFromHistory($boardId, $eventList);
		$board->join(new PlayerType($type), new PlayerToken($token), $name);
		$events = $board->getNotPersistedEventList();
		$persister->persistList($events);
		foreach($events as $event) {
			$eventPublisher->publish($event);
		}
		return new Response('', 201);
	}
	catch (RuntimeException $e) {
		return $app->abort(400, $e->getMessage());
	}
});


$app->post('/game/move/{uuid}', function(
	$uuid,
	Request $request,
	Application $app,
	EventPersisterInterface $persister,
	EventPublisherInterface $publisher,
	EventRepository $repository
) {
	$boardId = AggregateId::createFromString($uuid);
	$eventList = $repository->fetchListByAggregateId($boardId);
	if (!$eventList) {
		return $app->abort(404, 'Board not found');
	}
	if (!($token = $request->request->get('token'))) {
		return $app->abort(400, 'Missing token');
	}
	if (!($type = $request->request->get('type'))) {
		return $app->abort(400, 'Missing type');
	}
	if (($x = $request->request->get('x')) === null) {
		return $app->abort(400, 'Missing x');
	}
	if (($y = $request->request->get('y')) === null) {
		return $app->abort(400, 'Missing y');
	}

	$board = BoardAggregate::loadFromHistory($boardId, $eventList);
	try {
		$board->move(new PlayerType($type), new PlayerToken($token), $x, $y);
		$events = $board->getNotPersistedEventList();
		$persister->persistList($events);
		foreach($events as $event) {
			$publisher->publish($event);
		}
		return new Response('', 201);
	}
	catch (RuntimeException $e) {
		return $app->abort(400, $e->getMessage());
	}
});




$app->run();

