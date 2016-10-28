<?php

namespace EsTest\Event\Repository;

use Doctrine\DBAL\Connection;
use EsTest\AggregateId;
use EsTest\Event\DomainEvent;
use EsTest\Event\DomainEventList;

class EventRepository {

	private $connection;

	public function __construct(Connection $connection) {
		$this->connection = $connection;
	}

	public function fetchList() {
		$rows = $this->createQueryBuilder()
			->execute()
			->fetchAll();
		return $this->hydrateList($rows);
	}

	public function fetchListByAggregateId(AggregateId $aggregateId) {
		$rows = $this->createQueryBuilder()
			->where('e.aggregate_id = :aggregateId')
			->setParameter(':aggregateId', $aggregateId->toString())
			->execute()
			->fetchAll();

		return $this->hydrateList($rows);
	}

	private function createQueryBuilder() {
		return $this->connection->createQueryBuilder()
			->select('e.*')
			->from('event', 'e')
			->orderBy('e.id');
	}

	private function hydrateList(array $rows) {
		$events = [];
		foreach ($rows as $row) {
			$events[] = $this->hydrate($row);
		}
		return new DomainEventList($events);
	}

	private function hydrate(array $row) {
		/** @var DomainEvent $event */
		$event = unserialize($row['data']);
		$event->setId($row['id']);
		return $event;
	}
}