-- Purpose of this update was to assign a default value to the `usedby` column
ALTER TABLE `pcms_reg_keys` CHANGE `usedby` `usedby` INT( 11 ) NOT NULL DEFAULT '0' COMMENT 'The account ID of the user who registered with this code (for stat tracking purposes).';
UPDATE `pcms_versions` SET `value`='0.19' WHERE (`key`='database');