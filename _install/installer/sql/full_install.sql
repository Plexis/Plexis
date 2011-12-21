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
  `vote_data` text,
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
  `group_id` int(3) NOT NULL,
  `title` varchar(50) NOT NULL,
  `is_banned` tinyint(1) NOT NULL DEFAULT '0',
  `is_user` tinyint(1) NOT NULL DEFAULT '0',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcms_account_groups
-- ----------------------------
INSERT INTO `pcms_account_groups` VALUES ('0', 'Banned', '1', '0', '0', '0');
INSERT INTO `pcms_account_groups` VALUES ('1', 'Guest', '0', '0', '0', '0');
INSERT INTO `pcms_account_groups` VALUES ('2', 'Member', '0', '1', '0', '0');
INSERT INTO `pcms_account_groups` VALUES ('3', 'Admin', '0', '1', '1', '0');
INSERT INTO `pcms_account_groups` VALUES ('4', 'Super Admin', '0', '1', '1', '1');

-- ----------------------------
-- Table structure for `pcms_forum_categories`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_forum_categories`;
CREATE TABLE `pcms_forum_categories` (
  `cat_id` int(8) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) NOT NULL,
  `cat_privilages` varchar(255) NOT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_name_unique` (`cat_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pcms_forum_categories
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_forum_forums`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_forum_forums`;
CREATE TABLE `pcms_forum_forums` (
  `forum_id` int(8) NOT NULL AUTO_INCREMENT,
  `forum_name` varchar(255) NOT NULL DEFAULT 'Title Not Set!',
  `forum_description` varchar(255) DEFAULT 'No Description',
  `cat_id` int(8) NOT NULL DEFAULT '0' COMMENT 'The catagory ID this forum falls under',
  `last_topic_id` int(24) NOT NULL DEFAULT '0',
  `last_topic_title` varchar(255) DEFAULT NULL COMMENT 'Last forum topic title',
  `last_post_id` int(24) NOT NULL DEFAULT '0',
  `last_post_time` int(16) DEFAULT NULL COMMENT 'Last forum topic post time',
  `last_post_poster` varchar(255) DEFAULT NULL COMMENT 'last forum topic poster, ie: username',
  `total_topics` int(16) NOT NULL DEFAULT '0',
  `total_posts` int(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`forum_id`),
  UNIQUE KEY `cat_name_unique` (`forum_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pcms_forum_forums
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_forum_posts`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_forum_posts`;
CREATE TABLE `pcms_forum_posts` (
  `post_id` int(8) NOT NULL AUTO_INCREMENT,
  `post_content` text NOT NULL,
  `post_by` varchar(255) NOT NULL,
  `post_time` int(16) NOT NULL,
  `post_topic` int(8) NOT NULL,
  PRIMARY KEY (`post_id`),
  KEY `post_topic` (`post_topic`),
  KEY `post_by` (`post_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pcms_forum_posts
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_forum_topics`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_forum_topics`;
CREATE TABLE `pcms_forum_topics` (
  `topic_id` int(8) NOT NULL AUTO_INCREMENT,
  `topic_subject` varchar(255) NOT NULL,
  `topic_by` varchar(255) NOT NULL,
  `last_post_id` int(24) NOT NULL DEFAULT '0',
  `last_post_by` varchar(255) DEFAULT NULL,
  `last_post_time` int(16) NOT NULL DEFAULT '0',
  `topic_forum` int(8) NOT NULL,
  `topic_replies` int(5) NOT NULL DEFAULT '0',
  `topic_views` int(8) NOT NULL DEFAULT '0',
  `is_sticky` tinyint(4) NOT NULL DEFAULT '0',
  `is_global` tinyint(4) NOT NULL DEFAULT '0',
  `is_closed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `topic_cat` (`topic_forum`),
  KEY `topic_by` (`topic_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pcms_forum_topics
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_forum_unread`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_forum_unread`;
CREATE TABLE `pcms_forum_unread` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `forum_id` int(11) NOT NULL DEFAULT '0',
  `last_visit` int(16) NOT NULL DEFAULT '0',
  `topics_unread` varchar(500) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pcms_forum_unread
-- ----------------------------

-- ----------------------------
-- Table structure for `pcms_news`
-- ----------------------------
DROP TABLE IF EXISTS `pcms_news`;
CREATE TABLE `pcms_news` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
  `author` varchar(255) CHARACTER SET utf8 NOT NULL,
  `posted` varchar(255) CHARACTER SET utf8 NOT NULL,
  `body` text CHARACTER SET utf8,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pcms_news
-- ----------------------------
INSERT INTO `pcms_news` VALUES ('1', 'Welcome to Plexis CMS!', 'wilson212', '1324062007', '<p>Thank you for downloading Plexis Cms. Plexis is a professional WoW pirvate server CMS with tons of tools. Since we are in the Alpha stages, your feedback is <span style=\"color: #ff0000;\"><strong>critical</strong></span>. Please note that i donot recomend this site going live because of the due fact that there are not alot of features available.</p>');

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
  `key` varchar(40) NOT NULL,
  `assigned` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set to 1, this key has already been giving away, and waiting to be used.',
  `sponser` int(11) NOT NULL COMMENT 'Account ID of the sponser',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
