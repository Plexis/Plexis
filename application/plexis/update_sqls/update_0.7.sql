TRUNCATE TABLE `pcms_error_logs`;
ALTER TABLE `pcms_error_logs` CHANGE `level` `level` VARCHAR( 20 ) NOT NULL;
ALTER TABLE `pcms_error_logs` CHANGE `url` `url` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `pcms_error_logs` CHANGE `remote_ip` `remote_ip` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
UPDATE `pcms_versions` SET `value`='0.7' WHERE (`key`='database');