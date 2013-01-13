<?php
$routes = array (
	'error/404' => array(
        'error/Show404/index',
        'error/Show404/ajax'
    ),
    'error/403' => array(
        'error/Show403/index',
        'error/Show403/ajax'
    ),
    'error/offline' => array(
        'error/ShowOffline/index',
        'error/ShowOffline/ajax'
    ),
);
?>