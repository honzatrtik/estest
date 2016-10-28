<?php

namespace EsTest\Event\Persister;

use EsTest\Event\DomainEvent;
use EsTest\Event\DomainEventList;

interface EventPersisterInterface {

	public function persist(DomainEvent $event);
	public function persistList(DomainEventList $eventList);

}