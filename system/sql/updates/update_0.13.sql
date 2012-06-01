ALTER TABLE `pcms_realms` ADD `max_players` INT( 5 ) UNSIGNED NOT NULL DEFAULT '500' AFTER `type`;
UPDATE `pcms_versions` SET `value`='0.13' WHERE (`key`='database');