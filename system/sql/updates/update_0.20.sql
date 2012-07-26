ALTER TABLE `pcms_news`
	CHANGE COLUMN `posted`
	`posted` int(10) NOT NULL
AFTER `author`;
UPDATE `pcms_versions` SET `value`='0.20' WHERE (`key`='database');