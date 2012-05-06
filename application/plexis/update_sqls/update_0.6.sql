ALTER TABLE `pcms_hits` DROP COLUMN `hits`;
ALTER TABLE `pcms_hits` ADD COLUMN `lastseen` INT(11) NOT NULL DEFAULT '0';