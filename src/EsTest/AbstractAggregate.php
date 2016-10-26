<?php

namespace EsTest;

use EsTest\Event\DomainEvent;
use RuntimeException;

class AbstractAggregate {

	private $uncommitedEvents = [];

	protected function handle(DomainEvent $event, $isUncommited = false) {
		$eventName = $event->getEventName();
		$handler = [$this, 'handle' . ucfirst($eventName)];
		if (!is_callable($handler)) {
			throw new RuntimeException("Can not handle event '{$eventName}', ");
		}
		$handler($event);
		if ($isUncommited) {
			$this->uncommitedEvents[] = $event;
		}
	}

	public function getUncommitedEvents() {
		return $this->uncommitedEvents;
	}
}