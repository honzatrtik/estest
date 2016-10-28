<?php

namespace EsTest\Event\Persister;

use Doctrine\DBAL\Connection;
use EsTest\Event\DomainEvent;
use EsTest\Event\DomainEventList;
use Exception;

class EventPersister implements EventPersisterInterface {

	private $connection;

	public function __construct(Connection $connection) {
		$this->connection = $connection;
	}

	public function persist(DomainEvent $event) {
		$this->doPersist($event);
	}

	public function persistList(DomainEventList $eventList) {
		$this->connection->beginTransaction();
		try {
			foreach ($eventList as $event) {
				$this->doPersist($event);
			}
			$this->connection->commit();
		} catch (Exception $e) {
			$this->connection->rollBack();
			throw $e;
		}
	}

	private function doPersist(DomainEvent $event) {
		$this->connection->insert('event', $this->prepareData($event), ['created' => 'datetime']);
		$event->setId($this->connection->lastInsertId());
	}

	private function prepareData(DomainEvent $domainEvent) {
		return [
			'aggregate_id' => $domainEvent->getAggregateId()->toString(),
			'created' => $domainEvent->getCreated(),
			'data' => serialize($domainEvent),
		];
	}


}