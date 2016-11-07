<?php

return [

	'tempDir' => __DIR__ . '/../temp',
	'db.host' => 'mysql',
	'db.dbname' => 'estest',
	'db.user' => 'root',
	'db.password' => 'root',
	'db.port' => 3306,
	'db.driver' => 'mysqli',

	'db.params' => [
		'host' => DI\get('db.host'),
		'dbname' => DI\get('db.dbname'),
		'user' => DI\get('db.user'),
		'password' => DI\get('db.password'),
		'port' => DI\get('db.port'),
		'driver' => DI\get('db.driver'),
	],

	'rmq.host' => 'rabbitmq',

	'rmq.options' => [
		'host' => DI\get('rmq.host'),
	],

	'rethink.host' => 'rethinkdb',
];