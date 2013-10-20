CREATE TABLE `ngo20map`.`news` (
	`id` int NOT NULL AUTO_INCREMENT,
	`name` varchar(250),
	`url` varchar(250),
	`image` varchar(250),
	`type` varchar(10) DEFAULT 'news',
	PRIMARY KEY (`id`)
);

ALTER TABLE `ngo20map`.`news` ADD COLUMN `swffile` varchar(250) AFTER `type`;