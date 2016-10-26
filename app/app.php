<?php

use DI\Bridge\Silex\Application;
use EsTest\AggregateId;
use EsTest\BoardAggregate;
use EsTest\Event\DomainEvent;
use EsTest\Event\Repository\EventRepositoryInterface;
use EsTest\Player\Player;
use EsTest\Player\PlayerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @var Application $app */
$app = require_once __DIR__ . '/bootstrap.php';

$app->get('/events', function(Application $app, EventRepositoryInterface $eventRepository) {
	$data = array_map(function(DomainEvent $event) {
		return [
			'aggregateId' => (string) $event->getAggregateId(),
			'event' => $event->getEventName(),
			'created' => $event->getCreated()->format('Y-m-d H:i:s'),
		];
	}, $eventRepository->loadEvents());
	return $app->json($data);
});

$app->get('/uuid', function(Application $app) {
	return $app->json((string) AggregateId::create());
});

$app->post('/game/create/{uuid}', function($uuid, EventRepositoryInterface $eventRepository) {
	$board = BoardAggregate::create(AggregateId::createFromString($uuid));
	$eventRepository->saveEvents($board->getUncommitedEvents());
	return new Response('', 201);
});

$app->post('/game/join/{uuid}', function($uuid, Request $request, Application $app, EventRepositoryInterface $eventRepository) {
	$boardId = AggregateId::createFromString($uuid);
	$events = $eventRepository->loadEventsByAggregateId($boardId);
	if (!$events) {
		return $app->abort(404, 'Board not found');
	}
	if (!($token = $request->request->get('token'))) {
		return $app->abort(400, 'Missing token');
	}

	$board = BoardAggregate::loadFromHistory($boardId, $events);
	try {
		$playerType = new PlayerType($request->request->get('type'));
		$board->join(new Player($playerType, $token));
		$eventRepository->saveEvents($board->getUncommitedEvents());
		return new Response('', 201);
	}
	catch (RuntimeException $e) {
		return $app->abort(400, $e->getMessage());
	}
});


$app->post('/game/move/{uuid}', function($uuid, Request $request, Application $app, EventRepositoryInterface $eventRepository) {
	$boardId = AggregateId::createFromString($uuid);
	$events = $eventRepository->loadEventsByAggregateId($boardId);
	if (!$events) {
		return $app->abort(404, 'Board not found');
	}
	if (!($token = $request->request->get('token'))) {
		return $app->abort(400, 'Missing token');
	}
	if (!($x = $request->request->get('x'))) {
		return $app->abort(400, 'Missing x');
	}
	if (!($y = $request->request->get('y'))) {
		return $app->abort(400, 'Missing y');
	}

	$board = BoardAggregate::loadFromHistory($boardId, $events);
	try {
		$playerType = new PlayerType($request->request->get('type'));
		$board->move(new Player($playerType, $token), $x, $y);
		return new Response('', 201);
	}
	catch (RuntimeException $e) {
		return $app->abort(400, $e->getMessage());
	}
});




$app->run();

