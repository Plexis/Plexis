ALTER TABLE `pcms_accounts` CHANGE `web_points` `vote_points` INT( 10 ) NOT NULL DEFAULT '0';
ALTER TABLE `pcms_accounts` ADD `votes` INT( 5 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `selected_theme`;
ALTER TABLE `pcms_accounts` ADD `vote_points_earned` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `vote_points`;
ALTER TABLE `pcms_accounts` ADD `vote_points_spent` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `vote_points_earned`;
ALTER TABLE `pcms_accounts` ADD `donations` FLOAT( 7 ) NOT NULL DEFAULT '0.00' AFTER `vote_points_spent`;
UPDATE `pcms_versions` SET `value`='0.10' WHERE (`key`='database');