<?php

namespace EsTest\Event;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use RuntimeException;
use SplFixedArray;

class DomainEventList implements ArrayAccess, Countable, IteratorAggregate   {

	private $fixedArray;

	public function __construct($array) {
		$this->fixedArray = SplFixedArray::fromArray($array, false);
	}

	public function offsetExists($offset) {
		return $this->fixedArray->offsetExists($offset);
	}

	public function offsetGet($offset) {
		return $this->fixedArray->offsetGet($offset);
	}

	public function offsetSet($offset, $value) {
		throw new RuntimeException('Not implemented');
	}

	public function offsetUnset($offset) {
		throw new RuntimeException('Not implemented');
	}

	public function count() {
		return $this->fixedArray->count();
	}

	public function getIterator() {
		return $this->fixedArray;
	}

	public function map(callable $callback): self {
		return new static(array_map($callback, $this->fixedArray->toArray()));
	}

	public function toArray(): array {
		return $this->fixedArray->toArray();
	}
}