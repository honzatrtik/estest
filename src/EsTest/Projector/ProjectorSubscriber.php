<?php

namespace EsTest\Projector;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use EsTest\Event\DomainEvent;
use EsTest\Event\Publisher\EventPublisher;
use EsTest\Event\Repository\EventRepository;
use EsTest\Property\PropertyRepositoryInterface;

class ProjectorSubscriber {

	private $projectorId;
	private $bunny;
	private $projector;
	private $repository;
	private $propertyRepository;


	public function __construct(
		$projectorId,
		Client $bunny,
		ProjectorInterface $projector,
		EventRepository $repository,
		PropertyRepositoryInterface $propertyRepository
	) {
		$this->projectorId = $projectorId;
		$this->bunny = $bunny;
		$this->projector = $projector;
		$this->repository = $repository;
		$this->propertyRepository = $propertyRepository;
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
		return $this->propertyRepository->load(__CLASS__ . $this->projectorId);
	}

	protected function saveLastEventId($lastEventId) {
		$this->propertyRepository->save(__CLASS__ . $this->projectorId, $lastEventId);
	}









}