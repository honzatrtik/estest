<?php

namespace EsTest\Projector;

use EsTest\Event\DomainEvent;
use RuntimeException;

trait ProjectorHandlerTrait {

	public function handle(DomainEvent $event) {
		$eventName = $event->getEventName();
		$handler = [$this, 'handle' . ucfirst($eventName)];
		if (!is_callable($handler)) {
			throw new RuntimeException("Can not handle event '{$eventName}', ");
		}
		$handler($event);
	}

}