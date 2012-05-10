ALTER TABLE `pcms_hits` DROP COLUMN `hits`;
ALTER TABLE `pcms_hits` ADD COLUMN `lastseen` INT(11) NOT NULL DEFAULT '0';
UPDATE `pcms_versions` SET `value`='0.6' WHERE (`key`='database');