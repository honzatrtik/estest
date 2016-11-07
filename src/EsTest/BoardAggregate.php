<?php

namespace EsTest;

use EsTest\Event\BoardWasCreatedEvent;
use EsTest\Event\DomainEventList;
use EsTest\Event\PlayerMovedEvent;
use EsTest\Event\PlayerJoinedEvent;
use EsTest\Event\PlayerWonEvent;
use EsTest\Player\Player;
use EsTest\Player\PlayerToken;
use EsTest\Player\PlayerType;
use RuntimeException;

class BoardAggregate extends AbstractAggregate {

	const BOARD_WIDTH = 20;
	const BOARD_HEIGHT = 20;
	const WIN_COUNT = 5;

	private $boardId;
	private $board;
	private $players;
	private $moveCount;

	/** @var Player */
	private $winnerPlayer;

	/** @var PlayerType */
	private $lastMoveType;

	protected function __construct(AggregateId $boardId) {
		$this->boardId = $boardId;
		$this->board = [];
		$this->players = [];
		$this->moveCount = 0;
		$this->lastMoveType = null;
		$this->winnerPlayer = null;
	}


	public static function loadFromHistory(AggregateId $boardId, DomainEventList $events) {
		$board = new static($boardId);
		foreach ($events as $event) {
			$board->handle($event, true);
		}
		return $board;
	}

	public function create(AggregateId $boardId) {
		if (!empty($this->board)) {
			throw new RuntimeException("Board {$this->boardId} was already created");
		}
		$this->handle(new BoardWasCreatedEvent($boardId));
	}

	public function join(PlayerType $type, PlayerToken $token, $name) {
		if (isset($this->players[$type->getValue()])) {
			throw new RuntimeException("Player {$type->getValue()} already joined board {$this->boardId}");
		}

		$this->handle(new PlayerJoinedEvent($this->boardId, new Player($type, $token, $name)));
	}

	public function move(PlayerType $type, PlayerToken $token, $x, $y) {
		if (count($this->players) !== 2) {
			throw new RuntimeException("Both players must be joined to make move");
		}
		if ($this->lastMoveType && $this->lastMoveType === $type) {
			throw new RuntimeException("Player {$type->getValue()} can not move twice in a row");
		}
		if (!$this->isSamePlayerTypeToken($type, $token)) {
			throw new RuntimeException("Invalid token");
		}
		if ($this->winnerPlayer) {
			throw new RuntimeException("Game has already a winner");
		}
		if (!$this->isPositionValid($x, $y)) {
			throw new RuntimeException("Position out of board [{$x}, {$y}]");
		}
		if ($this->isPositionFree($x, $y)) {
			throw new RuntimeException("Position is not free [{$x}, {$y}]");
		}

		$player = $this->players[$type->getValue()];
		$this->handle(new PlayerMovedEvent($this->boardId, $player, $x, $y));
		if ($this->isFinalMove()) {
			$this->handle(new PlayerWonEvent($this->boardId, $player));
		}
	}

	private function isSamePlayerTypeToken(PlayerType $type, PlayerToken $token) {
		return $this->players[$type->getValue()]->getToken()->isEqual($token);
	}

	private function isPositionValid($x, $y) {
		return $x >= 0 && $x < static::BOARD_WIDTH && $y >= 0 && $y < static::BOARD_HEIGHT;
	}

	private function isPositionFree($x, $y) {
		return $this->board[$y][$x];
	}

	private function isFinalMove() {
		// Naive implementation
		foreach (range(0, static::BOARD_HEIGHT - 1) as $y) {
			if ($this->checkVector(0, $y, 1, 0)) {
				return true;
			}
			if ($this->checkVector(0, $y, 1, 1)) {
				return true;
			}
		}
		foreach (range(0, static::BOARD_WIDTH - 1) as $x) {
			if ($this->checkVector($x, 0, 0, 1)) {
				return true;
			}
			if ($this->checkVector($x, 0, -1, 1)) {
				return true;
			}
		}
		return false;
	}

	private function checkVector($x, $y, $dx, $dy, PlayerType $previousPlayerType = null, $count = 0) {
		if ($count === static::WIN_COUNT) {
			return true;
		}
		if (!$this->isPositionValid($x, $y)) {
			return false;
		}
		if (($playerType = $this->board[$y][$x]) && (!$previousPlayerType || $playerType === $previousPlayerType)) {
			return $this->checkVector($x + $dx, $y + $dy, $dx, $dy, $playerType, $count + 1);
		}
		return $this->checkVector($x + $dx, $y + $dy, $dx, $dy);
	}

	protected function handleBoardWasCreated(BoardWasCreatedEvent $event) {
		foreach (range(0, static::BOARD_HEIGHT - 1) as $i) {
			$this->board[$i] = array_fill(0, static::BOARD_WIDTH, null);
		}
	}

	protected function handlePlayerJoined(PlayerJoinedEvent $event) {
		$player = $event->getPlayer();
		$this->players[$player->getType()->getValue()] = $player;
	}

	protected function handlePlayerMoved(PlayerMovedEvent $event) {
		$this->board[$event->getY()][$event->getX()] = $event->getPlayer()->getType();
	}

	protected function handlePlayerWon(PlayerWonEvent $event) {
		$this->winnerPlayer = $event->getPlayer();
	}

	public function __toString() {
		$string = '';
		foreach ($this->board as $row) {
			/** @var PlayerType $position */
			foreach ($row as $position) {
				$string .= $position ? $position->getValue() : '.';
			}
			$string .= PHP_EOL;
		}
		return $string;
	}
}