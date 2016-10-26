<?php

namespace EsTest;

use EsTest\Event\BoardWasCreatedEvent;
use EsTest\Event\PlayerJoinedEvent;
use EsTest\Event\PlayerWonEvent;
use EsTest\Player\Player;
use EsTest\Player\PlayerType;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class BoardAggregateTest extends PHPUnit_Framework_TestCase {


	/**
	 * @return PHPUnit_Framework_MockObject_MockObject|AggregateId
	 */
	private function createAggregateId() {
		$aggregateId = $this->getMockBuilder(AggregateId::class)
			->disableOriginalConstructor()
			->getMock();
		return $aggregateId;
	}

	/**
	 * @return PHPUnit_Framework_MockObject_MockObject|Player
	 */
	private function createPlayer(PlayerType $playerType, $token) {
		$player = $this->getMockBuilder(Player::class)
			->disableOriginalConstructor()
			->getMock();
		$player->expects($this->any())->method('getType')->willReturn($playerType);
		$player->expects($this->any())->method('getToken')->willReturn($token);
		return $player;
	}

	public function testCreateBoard() {
		$board = BoardAggregate::create($this->createAggregateId());
		$this->assertInstanceOf(BoardAggregate::class, $board);

		$events = $board->getUncommitedEvents();
		$this->assertCount(1, $events);
		$this->assertInstanceOf(BoardWasCreatedEvent::class, $events[0]);
	}

	public function testJoinPlayers() {
		$board = BoardAggregate::create($this->createAggregateId());
		$playerX = $this->createPlayer(PlayerType::X(), 'abc');
		$playerO = $this->createPlayer(PlayerType::O(), 'efg');
		$board->join($playerX);
		$board->join($playerO);

		$events = $board->getUncommitedEvents();
		$this->assertCount(3, $events);

		$this->assertInstanceOf(BoardWasCreatedEvent::class, $events[0]);
		$this->assertInstanceOf(PlayerJoinedEvent::class, $events[1]);
		$this->assertInstanceOf(PlayerJoinedEvent::class, $events[1]);

		$this->assertEquals($playerX, $events[1]->getPlayer());
		$this->assertEquals($playerO, $events[2]->getPlayer());
	}

	public function testJoinPlayersAlreadyJoined() {
		$board = BoardAggregate::create($this->createAggregateId());
		$board->join($this->createPlayer(PlayerType::X(), 'abc'));
		try {
			$board->join($this->createPlayer(PlayerType::X(), 'abc'));
			$this->fail('Can not join the same player type more than once.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveWithoutBothPlayersJoined() {
		$board = BoardAggregate::create($this->createAggregateId());
		$playerX = $this->createPlayer(PlayerType::X(), 'abc');
		$board->join($playerX);
		try {
			$board->move($playerX, 1, 1);
			$this->fail('Can not make move without both players joined.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMovePlayerInvalidToken() {
		$board = BoardAggregate::create($this->createAggregateId());
		$playerX = $this->createPlayer(PlayerType::X(), 'abc');
		$playerO = $this->createPlayer(PlayerType::O(), 'cde');
		$board->join($playerX);
		$board->join($playerO);

		try {
			$playerX2 = $this->createPlayer(PlayerType::X(), 'abcd');
			$board->move($playerX2, 0, 0);
			$this->fail('Can not make move - invalid token.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveOutOfBoard() {
		$board = BoardAggregate::create($this->createAggregateId());
		$playerX = $this->createPlayer(PlayerType::X(), 'abc');
		$playerO = $this->createPlayer(PlayerType::O(), 'cde');
		$board->join($playerX);
		$board->join($playerO);

		try {
			$board->move($playerX, 0, BoardAggregate::BOARD_WIDTH);
			$this->fail('Can not make move out of board.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveSamePlayerTwice() {
		$board = BoardAggregate::create($this->createAggregateId());

		$playerX = $this->createPlayer(PlayerType::X(), 'abc');
		$playerO = $this->createPlayer(PlayerType::O(), 'cde');

		$board->join($playerX);
		$board->join($playerO);
		$board->move($playerX, 0, 0);

		try {
			$board->move($playerX, 0, 1);
			$this->fail('Same player can not make move twice in a row.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveToTakenPlace() {
		$board = BoardAggregate::create($this->createAggregateId());

		$playerX = $this->createPlayer(PlayerType::X(), 'abc');
		$playerO = $this->createPlayer(PlayerType::O(), 'cde');

		$board->join($playerX);
		$board->join($playerO);
		$board->move($playerX, 1, 1);

		try {
			$board->move($playerO, 1, 1);
			$this->fail('Can not place move to taken place.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveToWin() {
		$board = BoardAggregate::create($this->createAggregateId());

		$playerX = $this->createPlayer(PlayerType::X(), 'abc');
		$playerO = $this->createPlayer(PlayerType::O(), 'cde');

		$board->join($playerX);
		$board->join($playerO);
		$board->move($playerX, 0, 0);
		$board->move($playerO, 1, 1);
		$board->move($playerX, 0, 1);
		$board->move($playerO, 1, 3);
		$board->move($playerX, 0, 2);
		$board->move($playerO, 3, 3);
		$board->move($playerX, 0, 3);
		$board->move($playerO, 10, 3);
		$board->move($playerX, 0, 4);

		$events = $board->getUncommitedEvents();
		$lastEvent = end($events);
		$this->assertInstanceOf(PlayerWonEvent::class, $lastEvent);
		$this->assertEquals($playerX, $lastEvent->getPlayer());
	}

	public function testLoadFromHistory() {
		$board = BoardAggregate::loadFromHistory($this->createAggregateId(), []);
		$this->assertInstanceOf(BoardAggregate::class, $board);
	}

}
