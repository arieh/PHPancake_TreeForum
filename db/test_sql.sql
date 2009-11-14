/*
MySQL Data Transfer
Source Host: localhost
Source Database: pancake_tests
Target Host: localhost
Target Database: pancake_tests
Date: 09/11/2009 06:56:31
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for pancaketf_forums
-- ----------------------------
TRUNCATE TABLE `pancaketf_forums`;
-- ----------------------------
-- Table structure for pancaketf_message_contents
-- ----------------------------
TRUNCATE TABLE `pancaketf_message_contents`;
-- ----------------------------
-- Table structure for pancaketf_messages
-- ----------------------------
TRUNCATE TABLE `pancaketf_messages`;
-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `pancaketf_forums` VALUES ('1');
INSERT INTO `pancaketf_forums` VALUES ('2');
INSERT INTO `pancaketf_messages` VALUES ('1', '1', '1', '1',NOW());
INSERT INTO `pancaketf_messages` VALUES ('2', '1', '1.2', '1',NOW());
INSERT INTO `pancaketf_messages` VALUES ('3', '1', '1.3', '1',NOW());
INSERT INTO `pancaketf_messages` VALUES ('4', '1', '1.2.4', '1',NOW());
INSERT INTO `pancaketf_messages` VALUES ('5', '1', '5', '5',NOW());
INSERT INTO `pancaketf_messages` VALUES ('7', '1', '1.3.7', '1',NOW());
INSERT INTO `pancaketf_messages` VALUES ('8', '1', '5.8', '5',NOW());
INSERT INTO `pancaketf_messages` VALUES ('9', '1', '9', '9',NOW());
INSERT INTO `pancaketf_message_contents` VALUES ('1', 'a message', 'content');
INSERT INTO `pancaketf_message_contents` VALUES ('2', 'another message', 'content');
INSERT INTO `pancaketf_message_contents` VALUES ('3', 'message message', 'content');
INSERT INTO `pancaketf_message_contents` VALUES ('4', 'message message message', 'contnet');
INSERT INTO `pancaketf_message_contents` VALUES ('5', 'bla bla bla', 'longer content');
INSERT INTO `pancaketf_message_contents` VALUES ('7', 'some wierd title', 'test content');
INSERT INTO `pancaketf_message_contents` VALUES ('8', 'a test title', 'one before last');
INSERT INTO `pancaketf_message_contents` VALUES ('9', 'some other title', 'yet another');

