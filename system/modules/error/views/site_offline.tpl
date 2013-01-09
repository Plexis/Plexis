<?php defined('ROOT') or die('No Direct Access Allowed!'); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title>Site Offline</title>
	<style type="text/css"/>
        body {
            background: url("{site_url}/{root_dir}/img/site_offline.jpg") no-repeat top center #0f0f0f;
            color: #999999;
            padding-top: 460px;
        }
    </style>
</head>
<body>
    <div id="message"><center>{message}</center></div>
</body>
</html>