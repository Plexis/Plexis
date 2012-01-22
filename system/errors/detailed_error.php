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
			<b>Message:</b> {MESSAGE}<br /><br />
			<b>Reporting File:</b> {FILE}<br />
			<b>Line:</b> {LINE} <br /><br />
			
			<b><u>Debugging:</u></b><br /><br />
			
			<!-- the DEBUG you see will create a loop -->
			{DEBUG}
				<b>Backtrace Level {#}:</b><br />
				<b>File:</b> {FILE}<br />
				<b>Line:</b> {LINE} <br />
				<b>Class:</b> {CLASS} <br />
				<b>Function:</b> {FUNCTION} <br />
				<b>Function Args:</b> {ARGS} <br /><br />
			{/DEBUG}
		</div>
	</div>
</body>
</html>