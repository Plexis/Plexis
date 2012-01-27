SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `pcms_realms`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_realms`;
CREATE TABLE `pcms_realms` (
  `id` int(3) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(50) NOT NULL,
  `port` int(7) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'Icon',
  `driver` varchar(100) NOT NULL,
  `rates` text,
  `char_db` text NOT NULL,
  `world_db` text NOT NULL,
  `ra_info` text NOT NULL,
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_realms
-- ----------------------------

UPDATE `pcms_versions` SET `value`='0.2' WHERE (`key`='database');