DROP TABLE IF EXISTS `related_ngos`;
CREATE TABLE `related_ngos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `related_user_id` int(11) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `type` varchar(63) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

