<?php

namespace EsTest;

use EsTest\Event\BoardWasCreatedEvent;
use EsTest\Event\DomainEventList;
use EsTest\Event\PlayerJoinedEvent;
use EsTest\Event\PlayerWonEvent;
use EsTest\Player\Player;
use EsTest\Player\PlayerToken;
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
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);
		$this->assertInstanceOf(BoardAggregate::class, $board);

		$events = $board->getNotPersistedEventList();
		$this->assertCount(1, $events);
		$this->assertInstanceOf(BoardWasCreatedEvent::class, $events[0]);
	}

	public function testCreateAlreadyCreatedBoard() {
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);
		try {
			$board->create($boardId);
			$this->fail('Can not create already created board.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testJoinPlayers() {
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);
		$board->join(PlayerType::X(), new PlayerToken('abc'), 'Pepa');
		$board->join(PlayerType::O(), new PlayerToken('efg'), 'Jana');

		$events = $board->getNotPersistedEventList();
		$this->assertCount(3, $events);

		$this->assertInstanceOf(BoardWasCreatedEvent::class, $events[0]);
		$this->assertInstanceOf(PlayerJoinedEvent::class, $events[1]);
		$this->assertInstanceOf(PlayerJoinedEvent::class, $events[1]);

		$this->assertEquals('Pepa', $events[1]->getPlayer()->getName());
		$this->assertEquals('Jana', $events[2]->getPlayer()->getName());
		$this->assertEquals(PlayerType::X(), $events[1]->getPlayer()->getType());
		$this->assertEquals(PlayerType::O(), $events[2]->getPlayer()->getType());
	}

	public function testJoinPlayersAlreadyJoined() {
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);
		$board->join(PlayerType::X(), new PlayerToken('abc'), 'Pepa');
		try {
			$board->join(PlayerType::X(), new PlayerToken('cde'), 'Karel');
			$this->fail('Can not join the same player type more than once.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveWithoutBothPlayersJoined() {
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);
		$board->join(PlayerType::X(), new PlayerToken('abc'), 'Pepa');
		try {
			$board->move(PlayerType::X(), new PlayerToken('abc'), 1, 1);
			$this->fail('Can not make move without both players joined.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMovePlayerInvalidToken() {
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);
		$board->join(PlayerType::X(), new PlayerToken('abc'), 'Pepa');
		$board->join(PlayerType::O(), new PlayerToken('efg'), 'Jana');

		try {

			$board->move(PlayerType::O(), new PlayerToken('efg-something-else'), 0, 0);
			$this->fail('Can not make move - invalid token.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveOutOfBoard() {
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);
		$board->join(PlayerType::X(), new PlayerToken('abc'), 'Pepa');
		$board->join(PlayerType::O(), new PlayerToken('efg'), 'Jana');

		try {
			$board->move(PlayerType::O(), new PlayerToken('efg'), 0, BoardAggregate::BOARD_WIDTH);
			$this->fail('Can not make move out of board.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveSamePlayerTwice() {
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);

		$board->join(PlayerType::X(), new PlayerToken('abc'), 'Pepa');
		$board->join(PlayerType::O(), new PlayerToken('efg'), 'Jana');
		$board->move(PlayerType::O(), new PlayerToken('efg'), 0, 0);

		try {
			$board->move(PlayerType::O(), new PlayerToken('efg'), 0, 1);
			$this->fail('Same player can not make move twice in a row.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveToTakenPlace() {
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);

		$board->join(PlayerType::X(), new PlayerToken('abc'), 'Pepa');
		$board->join(PlayerType::O(), new PlayerToken('efg'), 'Jana');
		$board->move(PlayerType::O(), new PlayerToken('efg'), 1, 1);

		try {
			$board->move(PlayerType::X(), new PlayerToken('abc'), 1, 1);
			$this->fail('Can not place move to taken place.');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
	}

	public function testMoveToWin() {
		$boardId = $this->createAggregateId();
		$board = BoardAggregate::loadFromHistory($boardId, new DomainEventList([]));
		$board->create($boardId);

		$board->join(PlayerType::X(), new PlayerToken('abc'), 'Pepa');
		$board->join(PlayerType::O(), new PlayerToken('efg'), 'Jana');
		
		$board->move(PlayerType::X(), new PlayerToken('abc'), 0, 0);
		$board->move(PlayerType::O(), new PlayerToken('efg'), 1, 1);
		$board->move(PlayerType::X(), new PlayerToken('abc'), 0, 1);
		$board->move(PlayerType::O(), new PlayerToken('efg'), 1, 3);
		$board->move(PlayerType::X(), new PlayerToken('abc'), 0, 2);
		$board->move(PlayerType::O(), new PlayerToken('efg'), 3, 3);
		$board->move(PlayerType::X(), new PlayerToken('abc'), 0, 3);
		$board->move(PlayerType::O(), new PlayerToken('efg'), 10, 3);
		$board->move(PlayerType::X(), new PlayerToken('abc'), 0, 4);

		$events = $board->getNotPersistedEventList();
		$lastEvent = $events[count($events) - 1];
		$this->assertInstanceOf(PlayerWonEvent::class, $lastEvent);
		$this->assertEquals('Pepa', $lastEvent->getPlayer()->getName());
		$this->assertEquals(PlayerType::X(), $lastEvent->getPlayer()->getType());
	}

	public function testLoadFromHistory() {
		$board = BoardAggregate::loadFromHistory($this->createAggregateId(), new DomainEventList([]));
		$this->assertInstanceOf(BoardAggregate::class, $board);
	}

}
