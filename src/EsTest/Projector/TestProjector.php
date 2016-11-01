<?php

namespace EsTest\Projector;

use EsTest\Event\BoardWasCreatedEvent;
use EsTest\Event\DomainEvent;
use EsTest\Event\PlayerJoinedEvent;
use EsTest\Event\PlayerMovedEvent;
use EsTest\Event\PlayerWonEvent;

class TestProjector implements ProjectorInterface {

	use ProjectorHandlerTrait;

	public function reset() {
	}

	protected function handleBoardWasCreated(BoardWasCreatedEvent $event) {
		$this->log($event);
	}

	protected function handlePlayerJoined(PlayerJoinedEvent $event) {
		$this->log($event);
	}

	protected function handlePlayerMoved(PlayerMovedEvent $event) {
		$this->log($event);
	}

	protected function handlePlayerWon(PlayerWonEvent $event) {
		$this->log($event);
	}

	private function log(DomainEvent $event) {
		print $event->getEventName() . PHP_EOL;
	}
}