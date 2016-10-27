<?php

namespace EsTest;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;


class AggregateId {

	private $uuid;

	private function __construct(UuidInterface $uuid) {
		$this->uuid = Uuid::fromString($uuid);
	}

	public static function create() {
		return new static(Uuid::uuid1());
	}

	public static function createFromString($string) {
		return new static(Uuid::fromString($string));
	}

	public function isEqual(AggregateId $aggregateId) {
		return (string) $this === (string) $aggregateId;
	}

	public function toString() {
		return $this->uuid->toString();
	}

	public function __toString() {
		return $this->toString();
	}
}