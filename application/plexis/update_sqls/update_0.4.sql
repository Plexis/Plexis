ALTER TABLE `pcms_accounts` ADD COLUMN `registered` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `group_id`;

CREATE TABLE IF NOT EXISTS `pcms_hits` (
  `ip` bigint(20) unsigned NOT NULL DEFAULT '0',
  `page_url` varchar(200) NOT NULL DEFAULT '',
  `hits` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ip`,`page_url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

UPDATE `pcms_versions` SET `value`='0.4' WHERE (`key`='database');