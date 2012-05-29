<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title>Frostbite :: {ERROR_LEVEL}</title>
	<link rel="stylesheet" href="<?php echo $site_url; ?>/system/errors/css/general_error.css" type="text/css"/>
</head>
<body>
	<div id="container">
		<div class="header">{ERROR_LEVEL}</div>
		<div class="message">
            <p>
                <b>Message:</b> {MESSAGE}<br /><br />
                <b>Reporting File:</b> {FILE}<br />
                <b>Line:</b> {LINE} <br /><br />
            </p>
		</div>
	</div>
</body>
</html>