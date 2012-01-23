DROP TABLE IF EXISTS `pcms_account_groups`;
CREATE TABLE `pcms_account_groups` (
  `group_id` int(3) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `is_banned` tinyint(1) NOT NULL DEFAULT '0',
  `is_user` tinyint(1) NOT NULL DEFAULT '0',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `permissions` text COMMENT 'seriazlized array of permissions',
  PRIMARY KEY (`group_id`,`title`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO `pcms_account_groups` VALUES ('1', 'Guest', '0', '0', '0', '0', 'a:1:{s:12:\"admin_access\";i:0;}');
INSERT INTO `pcms_account_groups` VALUES ('2', 'Member', '0', '1', '0', '0', 'a:1:{s:12:\"admin_access\";i:0;}');
INSERT INTO `pcms_account_groups` VALUES ('3', 'Admin', '0', '1', '1', '0', 'a:1:{s:12:\"admin_access\";s:1:\"1\";}');
INSERT INTO `pcms_account_groups` VALUES ('4', 'Super Admin', '0', '1', '1', '1', 'a:1:{s:12:\"admin_access\";s:1:\"1\";}');
INSERT INTO `pcms_account_groups` VALUES ('5', 'Banned', '1', '0', '0', '0', 'a:1:{s:12:\"admin_access\";i:0;}');