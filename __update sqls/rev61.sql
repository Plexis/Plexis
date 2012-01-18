SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `pcms_account_groups`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_account_groups`;
CREATE TABLE `pcms_account_groups` (
  `group_id` int(3) NOT NULL,
  `title` varchar(50) NOT NULL,
  `is_banned` tinyint(1) NOT NULL DEFAULT '0',
  `is_user` tinyint(1) NOT NULL DEFAULT '0',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `permissions` text COMMENT 'seriazlized array of permissions',
  PRIMARY KEY (`group_id`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_account_groups
-- ----------------------------
INSERT INTO `pcms_account_groups` VALUES ('0', 'Banned', '1', '0', '0', '0', null);
INSERT INTO `pcms_account_groups` VALUES ('1', 'Guest', '0', '0', '0', '0', null);
INSERT INTO `pcms_account_groups` VALUES ('2', 'Member', '0', '1', '0', '0', null);
INSERT INTO `pcms_account_groups` VALUES ('3', 'Admin', '0', '1', '1', '0', null);
INSERT INTO `pcms_account_groups` VALUES ('4', 'Super Admin', '0', '1', '1', '1', null);

-- ----------------------------
-- Table structure for `pcms_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_permissions`;
CREATE TABLE `pcms_permissions` (
  `key` varchar(25) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `module` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_templates`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_templates`;
CREATE TABLE `pcms_templates` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'site' COMMENT 'site, forum, admin? Ability to assign types for templates',
  `author` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = Installed, 0 = Not Installed',
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_templates
-- ----------------------------
INSERT INTO `pcms_templates` VALUES ('1', 'default', 'site', 'test', '1');
