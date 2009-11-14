/*
MySQL Data Transfer
Source Host: localhost
Source Database: pancake_tests
Target Host: localhost
Target Database: pancake_tests
Date: 11/11/2009 14:19:04
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



-- ----------------------------
-- Table structure for pancaketf_messages
-- ----------------------------
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

-- ----------------------------
-- Table structure for pancaketf_message_contents
-- ----------------------------
DROP TABLE IF EXISTS `pancaketf_message_contents`;
CREATE TABLE `pancaketf_message_contents` (
  `message_id` int(10) unsigned NOT NULL,
  `title` varchar(45) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `fk_message_contents_messages1` (`message_id`),
  CONSTRAINT `fk_message_contents_messages1` FOREIGN KEY (`message_id`) REFERENCES `pancaketf_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;