<?php

namespace EsTest\Event;

use EsTest\AggregateId;
use EsTest\Player\Player;

class PlayerMovedEvent extends DomainEvent {

	const NAME = 'PlayerMoved';

	private $player;
	private $x;
	private $y;

	public function __construct(AggregateId $aggregateId, Player $player, $x, $y) {
		parent::__construct($aggregateId);
		$this->player = $player;
		$this->x = $x;
		$this->y = $y;
	}

	public function getPlayer() {
		return $this->player;
	}

	public function getX() {
		return $this->x;
	}

	public function getY() {
		return $this->y;
	}

	public function getEventName() {
		return static::NAME;
	}


}