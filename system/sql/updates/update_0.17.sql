ALTER TABLE `pcms_sessions` DROP COLUMN `user_data`;
ALTER TABLE `pcms_sessions` DROP COLUMN `last_seen`;
ALTER TABLE `pcms_sessions` ADD `expire_time` INT( 11 ) NOT NULL;
ALTER TABLE `pcms_accounts` DROP COLUMN `_session_id`;
UPDATE `pcms_versions` SET `value` = '0.17' WHERE `key` = 'database';