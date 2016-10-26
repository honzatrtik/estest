<?php

namespace EsTest\Event;

use EsTest\AggregateId;
use EsTest\Player\Player;

class PlayerWonEvent extends DomainEvent {

	const NAME = 'PlayerWon';

	private $player;

	public function __construct(AggregateId $aggregateId, Player $player) {
		parent::__construct($aggregateId);
		$this->player = $player;
	}

	public function getPlayer() {
		return $this->player;
	}

	public function getEventName() {
		return static::NAME;
	}
}