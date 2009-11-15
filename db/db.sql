/*
MySQL Data Transfer
Source Host: localhost
Source Database: pancake_tests
Target Host: localhost
Target Database: pancake_tests
Date: 15/11/2009 08:41:33
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for pancaketf_forums
-- ----------------------------
DROP TABLE IF EXISTS `pancaketf_forums`;
CREATE TABLE `pancaketf_forums` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pancaketf_message_contents`;
CREATE TABLE `pancaketf_message_contents` (
  `message_id` int(10) unsigned NOT NULL,
  `title` varchar(45) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `fk_message_contents_messages1` (`message_id`),
  CONSTRAINT `fk_message_contents_messages1` FOREIGN KEY (`message_id`) REFERENCES `pancaketf_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pancaketf_message_extras`;
CREATE TABLE `pancaketf_message_extras` (
  `message_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user` int(11) DEFAULT NULL,
  `votes` int(11) DEFAULT NULL,
  PRIMARY KEY (`message_id`),
  CONSTRAINT `pancaketf_message_extras_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `pancaketf_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pancaketf_messages`;
CREATE TABLE `pancaketf_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) NOT NULL,
  `dna` varchar(45) DEFAULT NULL,
  `base_id` int(11) DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`forum_id`),
  KEY `fk_messages_forums` (`forum_id`),
  CONSTRAINT `pancaketf_messages_ibfk_1` FOREIGN KEY (`forum_id`) REFERENCES `pancaketf_forums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

