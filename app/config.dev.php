<?php

return [
	'db.host' => '10.211.55.12',
	'db.dbname' => 'estest',
	'db.user' => 'root',
	'db.password' => 'root',
	'db.port' => 3307,
	'db.driver' => 'pdo_mysql',

	'db.params' => [
		'host' => DI\get('db.host'),
		'dbname' => DI\get('db.dbname'),
		'user' => DI\get('db.user'),
		'password' => DI\get('db.password'),
		'port' => DI\get('db.port'),
		'driver' => DI\get('db.driver'),
	],

	'rmq.host' => '10.211.55.12',

	'rmq.options' => [
		'host' => DI\get('rmq.host'),
	],

	'rethink.host' => '10.211.55.12',
];