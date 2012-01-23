SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `pcms_accounts`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_accounts`;
CREATE TABLE `pcms_accounts` (
  `id` int(12) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Email verified',
  `group_id` int(3) NOT NULL DEFAULT '2',
  `registration_ip` varchar(24) NOT NULL DEFAULT '0.0.0.0',
  `selected_theme` varchar(50) DEFAULT NULL,
  `web_points` int(10) NOT NULL DEFAULT '0',
  `_session_id` varchar(50) DEFAULT '',
  `_account_recovery` text COMMENT '// Hashed account revcovery question and answer',
  `_activation_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_accounts
-- ----------------------------

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
-- Table structure for `pcms_modules`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_modules`;
CREATE TABLE `pcms_modules` (
  `uri` varchar(255) NOT NULL,
  `name` varchar(255)  NOT NULL,
  `method` varchar(255) NOT NULL DEFAULT 'index',
  `has_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Has admin controller for this module',
  PRIMARY KEY (`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_modules
-- ----------------------------
INSERT INTO `pcms_modules` VALUES ('devtest/index', 'Devtest', 'index', '0');

-- ----------------------------
-- Table structure for `pcms_news`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_news`;
CREATE TABLE `pcms_news` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `posted` varchar(255) NOT NULL,
  `body` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_news
-- ----------------------------
INSERT INTO `pcms_news` VALUES ('1', 'Welcome to Plexis CMS!', 'wilson212', '1324062007', '<p>Thank you for downloading Plexis Cms. Plexis is a professional WoW pirvate server CMS with tons of tools. Since we are in the Alpha stages, your feedback is <span style=\"color: #ff0000;\"><strong>critical</strong></span>. Please note that i donot recomend this site going live because of the due fact that there are not alot of features available.</p>');

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
  `char_db` text NOT NULL,
  `world_db` text NOT NULL,
  `ra_info` text,
  `driver` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_realms
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_reg_keys`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_reg_keys`;
CREATE TABLE `pcms_reg_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(128) NOT NULL,
  `assigned` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set to 1, this key has already been giving away, and waiting to be used.',
  `sponser` int(11) NOT NULL COMMENT 'Account ID of the sponser',
  `usedby` int(11) NOT NULL COMMENT 'The account ID of the user who registered with this code (for stat tracking purposes).',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_reg_keys
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_sessions`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_sessions`;
CREATE TABLE `pcms_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` text,
  `ip_address` varchar(50) DEFAULT NULL,
  `last_seen` varchar(50) DEFAULT NULL,
  `user_data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_sessions
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

-- ----------------------------
-- Table structure for `pcms_vote_data`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_vote_data`;
CREATE TABLE `pcms_vote_data` (
  `account_id` int(11) DEFAULT NULL,
  `ip_address` varchar(50) NOT NULL,
  `data` text,
  PRIMARY KEY (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_vote_data
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_vote_sites`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_vote_sites`;
CREATE TABLE `pcms_vote_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(100) NOT NULL,
  `votelink` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `points` tinyint(3) NOT NULL DEFAULT '0',
  `reset_time` int(11) NOT NULL DEFAULT '43200' COMMENT 'Default reset time on voting. Default 12 hours',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_vote_sites
-- ----------------------------
