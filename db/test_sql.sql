TRUNCATE TABLE `pancaketf_forums`;

TRUNCATE TABLE  `pancaketf_message_contents`;

TRUNCATE TABLE `pancaketf_message_extras`;

TRUNCATE TABLE `pancaketf_messages`;

TRUNCATE TABLE `users`;


INSERT INTO `pancaketf_forums` VALUES ('1');
INSERT INTO `pancaketf_forums` VALUES ('2');
INSERT INTO `pancaketf_messages` VALUES ('1', '1', '1', '1', '2009-11-15 08:31:57');
INSERT INTO `pancaketf_messages` VALUES ('2', '1', '1.2', '1', '2009-11-15 08:31:57');
INSERT INTO `pancaketf_messages` VALUES ('3', '1', '1.3', '1', '2009-11-15 08:31:57');
INSERT INTO `pancaketf_messages` VALUES ('4', '1', '1.2.4', '1', '2009-11-15 08:31:57');
INSERT INTO `pancaketf_messages` VALUES ('5', '1', '5', '5', '2009-11-15 08:31:57');
INSERT INTO `pancaketf_messages` VALUES ('7', '1', '1.3.7', '1', '2009-11-15 08:31:57');
INSERT INTO `pancaketf_messages` VALUES ('8', '1', '5.8', '5', '2009-11-15 08:31:57');
INSERT INTO `pancaketf_messages` VALUES ('9', '1', '9', '9', '2009-11-15 08:31:57');
INSERT INTO `pancaketf_message_contents` VALUES ('1', 'a message', 'content');
INSERT INTO `pancaketf_message_contents` VALUES ('2', 'another message', 'content');
INSERT INTO `pancaketf_message_contents` VALUES ('3', 'message message', 'content');
INSERT INTO `pancaketf_message_contents` VALUES ('4', 'message message message', 'contnet');
INSERT INTO `pancaketf_message_contents` VALUES ('5', 'bla bla bla', 'longer content');
INSERT INTO `pancaketf_message_contents` VALUES ('7', 'some wierd title', 'test content');
INSERT INTO `pancaketf_message_contents` VALUES ('8', 'a test title', 'one before last');
INSERT INTO `pancaketf_message_contents` VALUES ('9', 'some other title', 'yet another');
INSERT INTO `pancaketf_message_extras` VALUES ('1', '1', '0');
INSERT INTO `pancaketf_message_extras` VALUES ('2', '1', '1');
INSERT INTO `pancaketf_message_extras` VALUES ('3', '2', '2');
INSERT INTO `pancaketf_message_extras` VALUES ('4', '1', '1');
INSERT INTO `pancaketf_message_extras` VALUES ('5', '2', '3');
INSERT INTO `pancaketf_message_extras` VALUES ('7', '2', '4');
INSERT INTO `pancaketf_message_extras` VALUES ('8', '2', '1');
INSERT INTO `pancaketf_message_extras` VALUES ('9', '1', '5');
INSERT INTO `users` VALUES ('1', 'arieh', 'arieh.glazer@gmail.com');
INSERT INTO `users` VALUES ('2', 'ita', 'some_email@gmail.com');
INSERT INTO `users` VALUES ('3', 'erez', 'another_email@gmail.com');
