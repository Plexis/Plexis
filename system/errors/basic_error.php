<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title>Frostbite :: {ERROR_LEVEL}</title>
	<link rel="stylesheet" href="<?php echo $site_url; ?>/system/errors/main.css" type="text/css"/>
</head>

<body>
	<div id="error-box">
		<div class="error-header">{ERROR_LEVEL}</div>
		<div class="error-message">
			<p>
				We are sorry for the inconvenience, but an unrecoverable error has occured. 
				If the problem persists, please notify the server administrator.
			</p>
			<b>Error Message:</b> {MESSAGE}
		</div>
	</div>
</body>
</html>