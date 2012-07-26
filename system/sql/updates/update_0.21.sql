-- ----------------------------
-- Table structure for `pcms_modules`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_modules`;
CREATE TABLE `pcms_modules` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT, 
  `uri1` varchar(24) NOT NULL,
  `uri2` varchar(50) NOT NULL,
  `name` varchar(24)  NOT NULL,
  `has_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Has admin controller for this module',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_modules
-- ----------------------------
INSERT INTO `pcms_modules` VALUES (1, 'devtest', '*', 'Devtest', '1');
UPDATE `pcms_versions` SET `value`='0.21' WHERE (`key`='database');