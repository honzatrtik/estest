<?php

namespace EsTest\Projector;

use EsTest\Event\BoardWasCreatedEvent;
use EsTest\Event\PlayerJoinedEvent;
use EsTest\Event\PlayerMovedEvent;
use EsTest\Event\PlayerWonEvent;
use r\Connection;
use r\ValuedQuery\ValuedQuery;
use r;

class RethinkProjector implements ProjectorInterface {

	use ProjectorHandlerTrait;

	private $connection;

	public function __construct(Connection $connection) {
		$this->connection = $connection;
	}

	public function reset() {
		r\table('board')->delete()->run($this->connection);
	}

	protected function handleBoardWasCreated(BoardWasCreatedEvent $event) {
		r\table('board')->insert([
			'id' => $event->getAggregateId()->toString(),
			'finished' => false,
			'players' => [],
			'board' => $this->getFilledArray(20, 20),
		])->run($this->connection);
	}

	protected function handlePlayerJoined(PlayerJoinedEvent $event) {
		$player = $event->getPlayer();
		$playerTypeValue = $player->getType()->getValue();
		r\table('board')->get($event->getAggregateId()->toString())
			->update([
				'players' => r\row('players')->append([$playerTypeValue => $player->getToken()])
			])
			->run($this->connection);
	}

	protected function handlePlayerMoved(PlayerMovedEvent $event) {
		$player = $event->getPlayer();
		$playerTypeValue = $player->getType()->getValue();
		$x = $event->getX();
		$y = $event->getY();
		r\table('board')->get($event->getAggregateId()->toString())
			->update(function(ValuedQuery $row) use ($x, $y, $playerTypeValue) {
				$board = $row->getField('board');
				return [
					'board' => $board->changeAt($y, $board(0)->changeAt($x, $playerTypeValue))
				];
			})
			->run($this->connection);
	}

	protected function handlePlayerWon(PlayerWonEvent $event) {
		$player = $event->getPlayer();
		$playerTypeValue = $player->getType()->getValue();
		r\table('board')->get($event->getAggregateId()->toString())->update([
			'winner' => [
				'type' => $playerTypeValue,
				'token' => $player->getToken(),
			],
		])->run($this->connection);
	}

	private function & getFilledArray($width, $height) {
		$board = [];
		foreach (range(0, $height - 1) as $i) {
			$board[$i] = array_fill(0, $width, null);
		}
		return $board;
	}

}