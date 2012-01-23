SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `pcms_account_groups`
-- ----------------------------
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

-- ----------------------------
-- Records of pcms_account_groups
-- ----------------------------
INSERT INTO `pcms_account_groups` VALUES ('1', 'Guest', '0', '0', '0', '0', 'a:0:{}');
INSERT INTO `pcms_account_groups` VALUES ('2', 'Member', '0', '1', '0', '0', 'a:1:{s:14:\"account_access\";s:1:\"1\";}');
INSERT INTO `pcms_account_groups` VALUES ('3', 'Admin', '0', '1', '1', '0', 'a:5:{s:12:\"admin_access\";s:1:\"1\";s:12:\"manage_users\";s:1:\"1\";s:11:\"manage_news\";s:1:\"1\";s:21:\"send_console_commands\";s:1:\"1\";s:14:\"account_access\";s:1:\"1\";}');
INSERT INTO `pcms_account_groups` VALUES ('4', 'Super Admin', '0', '1', '1', '1', 'a:0:{}');
INSERT INTO `pcms_account_groups` VALUES ('5', 'Banned', '1', '0', '0', '0', 'a:0:{}');

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
INSERT INTO `pcms_permissions` VALUES ('account_access', 'Access to Account', 'Allow this user to login and access his account?', 'core');
INSERT INTO `pcms_permissions` VALUES ('admin_access', 'Admin Panel Access', 'Allow this user access to the admin panel?', 'admin');
INSERT INTO `pcms_permissions` VALUES ('manage_admins', 'Manage  Admin Accounts', 'Allow this group to manage and edit admin groups in the admin panel?', 'admin');
INSERT INTO `pcms_permissions` VALUES ('manage_modules', 'Manage Modules', 'Allow this user to manage modules installed in the cms?', 'admin');
INSERT INTO `pcms_permissions` VALUES ('manage_news', 'Post / Edit Frontpage News', 'Allow this group to Post and Edit frontpage news?', 'admin');
INSERT INTO `pcms_permissions` VALUES ('manage_realms', 'Manage Realms', 'Allow this group to Install/Edit realms in the admin panel?', 'admin');
INSERT INTO `pcms_permissions` VALUES ('manage_site_config', 'Manage Site Settings & Configuration', 'Allow this group to change the site configuration settings?', 'admin');
INSERT INTO `pcms_permissions` VALUES ('manage_templates', 'Manage Templates', 'Allow this group to Install / Unistall site templates?', 'admin');
INSERT INTO `pcms_permissions` VALUES ('manage_users', 'Manage User Accounts', 'Allow this group to manage and edit users in the admin panel?', 'admin');
INSERT INTO `pcms_permissions` VALUES ('manage_votesites', 'Manage Vote Sites', 'Allow this user group to manage votesites in the admin panel?', 'admin');
INSERT INTO `pcms_permissions` VALUES ('send_console_commands', 'Send Console Commands', 'Allow this group access to the RA command console in the admin panel?', 'admin');
