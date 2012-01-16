SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `pcms_sessions`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_sessions`;
CREATE TABLE `pcms_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(25) NOT NULL DEFAULT '',
  `ip_address` varchar(50) DEFAULT NULL,
  `last_seen` varchar(50) DEFAULT NULL,
  `user_data` text,
  PRIMARY KEY (`id`,`token`)
) DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_sessions
-- ----------------------------
