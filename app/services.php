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
use EsTest\Property\FilePropertyRepository;
use EsTest\Property\PropertyRepositoryInterface;
use Interop\Container\ContainerInterface;

return [
	Connection::class => factory([DriverManager::class, 'getConnection'])
		->parameter('params', get('db.params')),
	EventPersisterInterface::class => object(EventPersister::class),
	EventPublisherInterface::class => object(EventPublisher::class),
	PropertyRepositoryInterface::class => object(FilePropertyRepository::class)
		->constructorParameter('directory', get('tempDir')),
	Client::class => object(Client::class)
		->constructorParameter('options', get('rmq.options'))
		->method('connect'),
	r\Connection::class => function(ContainerInterface $c) {
		return r\connect($c->get('rethink.host'));
	},
];
