<?php

namespace EsTest\Event\Publisher;

use Bunny\Client;
use EsTest\Event\DomainEvent;

class EventPublisher implements EventPublisherInterface {

	const EXCHANGE_NAME = 'events';

	private $bunny;

	public function __construct(Client $bunny) {
		$this->bunny = $bunny;
	}

	public function publish(DomainEvent $event) {
		$channel = $this->bunny->channel();
		$channel->exchangeDeclare(static::EXCHANGE_NAME, 'topic');
		$channel->publish(serialize($event), [], static::EXCHANGE_NAME, 'event');
	}
}