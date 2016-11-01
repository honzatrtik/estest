<?php

namespace EsTest\Projector;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use EsTest\Event\DomainEvent;
use EsTest\Event\Publisher\EventPublisher;
use EsTest\Event\Repository\EventRepository;

class ProjectorSubscriber {

	private $projectorId;
	private $bunny;
	private $projector;
	private $repository;


	public function __construct($projectorId, Client $bunny, ProjectorInterface $projector, EventRepository $repository) {
		$this->projectorId = $projectorId;
		$this->bunny = $bunny;
		$this->projector = $projector;
		$this->repository = $repository;
	}

	public function reset() {
		$this->saveLastEventId(null);
		$this->projector->reset();
	}

	public function subscribe() {

		// Subscribe to new events from exchange
		$queueName = $this->projectorId;
		$channel = $this->bunny->channel();
		$channel->exchangeDeclare(EventPublisher::EXCHANGE_NAME, 'topic');
		$channel->queueDeclare($queueName);
		$channel->queueBind($queueName, EventPublisher::EXCHANGE_NAME, '#');

		// Load all previous events according to last handled eventId
		$lastEventId = $this->loadLastEventId();
		$events = $lastEventId
			? $this->repository->fetchListEventIdGreaterThan($lastEventId)
			: $this->repository->fetchList();

		foreach ($events as $event) {
			$this->handle($event, $lastEventId);
		}

		// Continue handling messages from the exchange
		$channel->run(function(Message $message, Channel $channel, Client $bunny) {
			/** @var DomainEvent $event */
			$event = unserialize($message->content);
			$this->handle($event, $this->loadLastEventId());
			$channel->ack($message);
		}, $queueName);
	}

	protected function handle(DomainEvent $event, $lastEventId) {
		if ($event->getId() > $lastEventId) {
			$this->projector->handle($event);
			$this->saveLastEventId($event->getId());
		}
	}

	protected function loadLastEventId() {
		$file = __DIR__ . "/projector-{$this->projectorId}";
		return file_exists($file)
			? (int) file_get_contents($file)
			: null;
	}

	protected function saveLastEventId($lastEventId) {
		$file = __DIR__ . "/projector-{$this->projectorId}";
		file_put_contents($file, $lastEventId);
	}









}