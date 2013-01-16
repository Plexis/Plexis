<?php
$routes = array(
    'admin/modules/manage/(:any)' => 'admin/modules/manage/$1', // Dont define an ajax controller here!
	'admin/modules/?(:any)' => array(
        'admin/modules/$1',
        'admin/Modules_Ajax/$1'
    )
);
?>