<?php

namespace EsTest;

use EsTest\Event\DomainEvent;
use EsTest\Event\DomainEventList;
use RuntimeException;

class AbstractAggregate {

	private $notPersistedEvents = [];

	protected function handle(DomainEvent $event, $persisted = false) {
		$eventName = $event->getEventName();
		$handler = [$this, 'handle' . ucfirst($eventName)];
		if (!is_callable($handler)) {
			throw new RuntimeException("Can not handle event '{$eventName}', ");
		}
		$handler($event);
		if (!$persisted) {
			$this->notPersistedEvents[] = $event;
		}
	}

	public function getNotPersistedEventList() {
		return new DomainEventList($this->notPersistedEvents);
	}
}