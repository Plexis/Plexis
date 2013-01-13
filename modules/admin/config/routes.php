<?php
$routes = array(
	'admin/modules/?(:any)' => array(
        'admin/modules/$1',
        'admin/Modules_Ajax/$1'
    )
);
?>