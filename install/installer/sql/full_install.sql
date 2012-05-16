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
  `last_seen` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `registration_ip` varchar(24) NOT NULL DEFAULT '0.0.0.0',
  `selected_theme` varchar(50) DEFAULT NULL,
  `votes` int(5) unsigned NOT NULL DEFAULT '0',
  `vote_points` int(10) NOT NULL DEFAULT '0',
  `vote_points_earned` int(10) unsigned NOT NULL DEFAULT '0',
  `vote_points_spent` int(10) unsigned NOT NULL DEFAULT '0',
  `donations` float NOT NULL DEFAULT '0',
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
INSERT INTO `pcms_account_groups` (`group_id`, `title`, `is_banned`, `is_user`, `is_admin`, `is_super_admin`, `permissions`) VALUES
(1, 'Guest', 0, 0, 0, 0, 'a:0:{}'),
(2, 'Member', 0, 1, 0, 0, 'a:3:{s:14:"account_access";s:1:"1";s:12:"update_email";s:1:"1";s:15:"update_password";s:1:"1";}'),
(3, 'Admin', 0, 1, 1, 0, 'a:11:{s:12:"admin_access";s:1:"1";s:12:"manage_users";s:1:"1";s:11:"manage_news";s:1:"1";s:21:"send_console_commands";s:1:"1";s:16:"ban_user_account";s:1:"1";s:19:"delete_user_account";s:1:"1";s:17:"manage_characters";s:1:"1";s:17:"delete_characters";s:1:"1";s:14:"account_access";s:1:"1";s:12:"update_email";s:1:"1";s:15:"update_password";s:1:"1";}'),
(4, 'Super Admin', 0, 1, 1, 1, 'a:0:{}'),
(5, 'Banned', 1, 0, 0, 0, 'a:0:{}');

-- ----------------------------
-- Table structure for `pcms_admin_logs`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_admin_logs`;
CREATE TABLE `pcms_admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `desc` text,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_admin_logs
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_error_logs`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_error_logs`;
CREATE TABLE `pcms_error_logs` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `level` varchar(20) NOT NULL,
  `string` text NOT NULL,
  `file` text NOT NULL,
  `line` int(5) NOT NULL,
  `url` text,
  `remote_ip` varchar(20) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `backtrace` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_error_logs
-- ----------------------------

-- ----------------------------
-- Table structure for table `pcms_hits`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_hits`;
CREATE TABLE `pcms_hits` (
  `ip` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lastseen` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_hits
-- ----------------------------

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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(25) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `module` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23;

-- ----------------------------
-- Records of pcms_permissions
-- ----------------------------
INSERT INTO `pcms_permissions` (`id`, `key`, `name`, `description`, `module`) VALUES
(1, 'admin_access', 'Admin Panel Access', 'Allow this user access to the admin panel?', 'admin'),
(2, 'manage_users', 'Manage User Accounts', 'Allow this group to manage and edit users in the admin panel?', 'admin'),
(3, 'manage_admins', 'Manage Admin Accounts', 'Allow this group to manage and edit admin groups in the admin panel?', 'admin'),
(4, 'ban_user_account', 'Ban User Accounts', 'Allow this user group to ban user level accounts?', 'admin'),
(5, 'ban_admin_account', 'Ban Admin Account', 'Allow this user group to ban admin level groups?', 'admin'),
(6, 'delete_user_account', 'Delete User Accounts', 'Allow this user group to delete user level accounts?', 'admin'),
(7, 'delete_admin_account', 'Delete Admin Accounts', 'Allow this user group to delete admin level accounts?', 'admin'),
(8, 'manage_characters', 'Edit Characters', 'Allow this user group to edit characters?', 'admin'),
(9, 'delete_characters', 'Delete Characters', 'Allow this user group to delete characters?', 'admin'),
(10, 'manage_modules', 'Manage Modules', 'Allow this user to manage modules installed in the cms?', 'admin'),
(11, 'manage_news', 'Post / Edit Frontpage News', 'Allow this group to Post and Edit frontpage news?', 'admin'),
(12, 'manage_realms', 'Manage Realms', 'Allow this group to Install/Edit realms in the admin panel?', 'admin'),
(13, 'manage_site_config', 'Manage Site Settings & Configuration', 'Allow this group to change the site configuration settings?', 'admin'),
(14, 'manage_templates', 'Manage Templates', 'Allow this group to Install / Unistall site templates?', 'admin'),
(15, 'manage_votesites', 'Manage Vote Sites', 'Allow this user group to manage votesites in the admin panel?', 'admin'),
(16, 'send_console_commands', 'Send Console Commands', 'Allow this group access to the RA command console in the admin panel?', 'admin'),
(17, 'manage_error_logs', 'Manage Error Logs', 'Allow this user group to view/delete error logs?', 'admin'),
(18, 'view_admin_logs', 'View Admin Logs', 'Allow this user group to view admin logs?', 'admin'),
(19, 'delete_admin_logs', 'Delete Admin Logs', 'Allow this user group to delete admin action logs?', 'admin'),
(20, 'account_access', 'Access to Account', 'Allow this user to login and access his account?', 'core'),
(21, 'update_email', 'Change Account Email', 'Allow this user group to change their email address?', 'core'),
(22, 'update_password', 'Change Account  Password', 'Is this user group allowed to change thier password?', 'core'),
(23, 'create_invite_keys', 'Create Invite Keys', 'Allow this user group to create Invite Keys to give to unregistered users?', 'core');

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
  `max_players` int(5) UNSIGNED NOT NULL DEFAULT '500',
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

-- ----------------------------
-- Table structure for `pcms_reg_keys`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_reg_keys`;
CREATE TABLE `pcms_reg_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(30) NOT NULL,
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
-- Table structure for `pcms_versions`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_versions`;
CREATE TABLE `pcms_versions` (
  `key` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_versions
-- ----------------------------
INSERT INTO `pcms_versions` VALUES ('database', '0.14');

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
