ALTER TABLE `pcms_accounts` ADD COLUMN `last_seen` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `group_id`;

UPDATE `pcms_versions` SET `value`='0.5' WHERE (`key`='database');