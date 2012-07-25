ALTER TABLE `pcms_news`
	CHANGE COLUMN `posted`
	`posted` int(10) NOT NULL
AFTER `author`;