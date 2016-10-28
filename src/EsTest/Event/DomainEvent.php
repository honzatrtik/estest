<?php

namespace EsTest\Event;

use DateTimeImmutable;
use EsTest\AggregateId;

abstract class DomainEvent {

	protected $id;

	/** @var AggregateId */
	protected $aggregateId;

	/** @var DateTimeImmutable */
	protected $created;

	public function __construct(AggregateId $aggregateId) {
		$this->aggregateId = $aggregateId;
		$this->created = new DateTimeImmutable();
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getAggregateId() {
		return $this->aggregateId;
	}

	public function getCreated() {
		return $this->created;
	}

	/** @return string */
	abstract function getEventName();
}