<?php
/*
| ---------------------------------------------------------------
| DB_configs
| ---------------------------------------------------------------
|
| $DB_configs is an array of database configurations for the
| loader class to load. In the default configuration, 'DB'
| is the indentifier, which is loaded like so:
| $this->load->database( 'DB' );
|
| The above example will use that first param as the
| identifier in which it should load. If no identifier
| is given in the loader, the first in the array is loaded
|
*/
$DB_configs = array(
	'DB' => array(
		'driver'	   => 'mysql',
		'host'         => 'localhost',
		'port'         => '3306',
		'username'     => 'root',
		'password'     => 'ascent',
		'database'     => 'framework'
	),
	'RDB' => array(
		'driver'	   => 'mysql',
		'host'         => 'localhost',
		'port'         => '3306',
		'username'     => 'root',
		'password'     => 'ascent',
		'database'     => 'auth'
	)
);
?>