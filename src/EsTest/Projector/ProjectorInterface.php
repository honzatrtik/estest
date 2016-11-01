<?php

namespace EsTest\Projector;

use EsTest\Event\DomainEvent;

interface ProjectorInterface {
	public function handle(DomainEvent $event);
	public function reset();
}