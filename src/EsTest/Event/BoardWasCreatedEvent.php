<?php

namespace EsTest\Event;

class BoardWasCreatedEvent extends DomainEvent {

	const NAME = 'BoardWasCreated';

	public function getEventName() {
		return static::NAME;
	}
}