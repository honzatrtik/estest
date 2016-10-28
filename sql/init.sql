CREATE TABLE `event` (
  `id` BIGINT unsigned NOT NULL AUTO_INCREMENT,
  `aggregate_id` VARCHAR(64) NOT NULL,
  `data` TEXT NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_aggregate_id` (`aggregate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;