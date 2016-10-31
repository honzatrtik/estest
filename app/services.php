<?php


use Bunny\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use EsTest\Event\Persister\EventPersister;
use EsTest\Event\Persister\EventPersisterInterface;
use EsTest\Event\Publisher\EventPublisher;
use EsTest\Event\Publisher\EventPublisherInterface;
use function DI\factory;
use function DI\get;
use function DI\object;

return [
	Connection::class => factory([DriverManager::class, 'getConnection'])
		->parameter('params', get('db.params')),
	EventPersisterInterface::class => object(EventPersister::class),
	EventPublisherInterface::class => object(EventPublisher::class),
	Client::class => object(Client::class)
		->constructorParameter('options', get('rmq.options'))
		->method('connect'),
];
