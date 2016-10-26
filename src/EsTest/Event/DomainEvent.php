<?php

namespace EsTest\Event;

use DateTimeImmutable;
use EsTest\AggregateId;

abstract class DomainEvent {

	/** @var AggregateId */
	protected $aggregateId;

	/** @var DateTime */
	protected $created;

	public function __construct(AggregateId $aggregateId) {
		$this->aggregateId = $aggregateId;
		$this->created = new DateTimeImmutable(); // It'd be better with microtime support
	}

	public function getAggregateId() {
		return $this->aggregateId;
	}

	/**
	 * @return DateTimeImmutable
	 */
	public function getCreated() {
		return $this->created;
	}

	/** @return string */
	abstract function getEventName();
}