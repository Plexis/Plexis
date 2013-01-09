ALTER TABLE  `pcms_routes` ADD  `core` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  'Is this a core module?'
INSERT INTO `pcms_routes` (`module_param`, `action_param`, `module`, `controller`, `method`, `core`) 
VALUES ('error', '403', 'error', 'Show403', 'index', '1'), 
('error', '404', 'error', 'Show404', 'index', '1')
('error', 'offline', 'error', 'SiteOffline', 'index', '1');