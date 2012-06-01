ALTER TABLE `pcms_accounts` ADD `language` VARCHAR( 20 ) DEFAULT NULL AFTER `registration_ip`;
UPDATE `pcms_versions` SET `value`='0.15' WHERE (`key`='database');