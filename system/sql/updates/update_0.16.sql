UPDATE `pcms_modules` SET `has_admin` = 1 WHERE `name` = 'Devtest' LIMIT 1;
UPDATE `pcms_versions` SET `value` = '0.16' WHERE `key` = 'database';