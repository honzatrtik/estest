<?php

namespace EsTest\Event\Publisher;

use EsTest\Event\DomainEvent;

interface EventPublisherInterface {

	public function publish(DomainEvent $event);

}