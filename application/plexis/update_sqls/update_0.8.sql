ALTER TABLE `pcms_admin_logs` CHANGE `time` `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
UPDATE `pcms_versions` SET `value`='0.8' WHERE (`key`='database');