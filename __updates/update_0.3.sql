SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `pcms_admin_logs`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_admin_logs`;
CREATE TABLE `pcms_admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `desc` text,
  `time` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_admin_logs
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_account_logs`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_account_logs`;
CREATE TABLE `pcms_account_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `desc` text,
  `time` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_account_logs
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_error_logs`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_error_logs`;
CREATE TABLE `pcms_error_logs` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `level` int(4) NOT NULL,
  `string` text NOT NULL,
  `file` text NOT NULL,
  `line` int(5) NOT NULL,
  `url` varchar(512) DEFAULT NULL,
  `remote_ip` varchar(128) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `backtrace` text,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_error_logs
-- ----------------------------

UPDATE `pcms_versions` SET `value`='0.3' WHERE (`key`='database');