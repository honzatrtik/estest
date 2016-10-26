<?php

namespace EsTest\Event\Repository;

use EsTest\AggregateId;
use EsTest\Event\DomainEvent;
use RuntimeException;

class FileEventRepository implements EventRepositoryInterface {

	private $filePath;

	public function __construct($filePath) {
		$this->filePath = $filePath;
	}

	public function saveEvents(array $events) {
		$fp = fopen($this->filePath, 'a');
		if (!flock($fp, LOCK_EX)) {
			throw new RuntimeException('Can not acquire lock');
		}
		foreach ($events as $event) {
			fputs($fp, serialize($event) . PHP_EOL);
		}
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	private function doLoadEvents(callable $filter = null) {
		$fp = fopen($this->filePath, 'r');
		$events = [];
		while (($data = fgets($fp, 4096)) !== false) {
			/** @var DomainEvent $event */
			$event = unserialize(trim($data));
			if (!$filter || $filter($event)) {
				$events[] = $event;
			}
		}
		if (!feof($fp)) {
			throw new RuntimeException('Unexpected fgets fail.');
		}
		fclose($fp);
		return $events;
	}

	public function loadEvents() {
		return $this->doLoadEvents();
	}

	public function loadEventsByAggregateId(AggregateId $aggregateId) {
		return $this->doLoadEvents(function(DomainEvent $event) use ($aggregateId) {
			return $event->getAggregateId()->isEqual($aggregateId);
		});
	}
}