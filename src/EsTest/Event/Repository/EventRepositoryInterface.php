<?php

namespace EsTest\Event\Repository;

use EsTest\AggregateId;

interface EventRepositoryInterface {

	/**
	 * This should be in transaction
	 */
	public function saveEvents(array $events);
	public function loadEvents();
	public function loadEventsByAggregateId(AggregateId $aggregateId);

}