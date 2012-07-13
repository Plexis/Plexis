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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24;

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
(11, 'manage_plugins', 'Manage Plugins', 'Allow this user group to install and remove plugins via the admin panel?', 'admin'),
(12, 'manage_news', 'Post / Edit Frontpage News', 'Allow this group to Post and Edit frontpage news?', 'admin'),
(13, 'manage_realms', 'Manage Realms', 'Allow this group to Install/Edit realms in the admin panel?', 'admin'),
(14, 'manage_site_config', 'Manage Site Settings & Configuration', 'Allow this group to change the site configuration settings?', 'admin'),
(15, 'manage_templates', 'Manage Templates', 'Allow this group to Install / Unistall site templates?', 'admin'),
(16, 'manage_votesites', 'Manage Vote Sites', 'Allow this user group to manage votesites in the admin panel?', 'admin'),
(17, 'send_console_commands', 'Send Console Commands', 'Allow this group access to the RA command console in the admin panel?', 'admin'),
(18, 'manage_error_logs', 'Manage Error Logs', 'Allow this user group to view/delete error logs?', 'admin'),
(19, 'view_admin_logs', 'View Admin Logs', 'Allow this user group to view admin logs?', 'admin'),
(20, 'delete_admin_logs', 'Delete Admin Logs', 'Allow this user group to delete admin action logs?', 'admin'),
(21, 'account_access', 'Access to Account', 'Allow this user to login and access his account?', 'core'),
(22, 'update_email', 'Change Account Email', 'Allow this user group to change their email address?', 'core'),
(23, 'update_password', 'Change Account  Password', 'Is this user group allowed to change thier password?', 'core'),
(24, 'create_invite_keys', 'Create Invite Keys', 'Allow this user group to create Invite Keys to give to unregistered users?', 'core');

UPDATE `pcms_versions` SET `value`='0.18' WHERE (`key`='database');