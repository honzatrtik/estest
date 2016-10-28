<?php


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use EsTest\Event\Persister\EventPersister;
use EsTest\Event\Persister\EventPersisterInterface;


return [
	Connection::class => DI\factory([DriverManager::class, 'getConnection'])
		->parameter('params', DI\get('db.params')),
	EventPersisterInterface::class => DI\object(EventPersister::class)
];
