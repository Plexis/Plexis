<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title>PlexisCore :: {ERROR_LEVEL}</title>
	<style type="text/css">
        body
        {
            background: #454545;
            padding: 30px;
            margin: 0;
        }
        #container {
            width: 800px;
            background-color: #7a0b0b;
            margin-left: auto;
            margin-right: auto;
            border: 1px solid #9d0e0e;
            -moz-border-radius: 6px;
            border-radius: 6px;
            -webkit-border-radius: 6px;	
            -moz-box-shadow: 0 0 5px 5px #333;
            -webkit-box-shadow: 0 0 5px 5px#333;
            box-shadow: 0 0 5px 5px #333;
        }
        div.header {
            margin: 10px;
            font-weight: bold;
            font-size: 24px;
            color: #f3f3f3;
            text-shadow: 2px 2px 5px #200404;
            border-bottom: 1px solid #9d0e0e;
        }
        div.message {
            color: #f8f8f8;
            text-shadow: 1px 2px 2px #200404;
            margin:10px;
            font-size: 16px;
        }
        div.links {
            border-top: 1px solid #666666;
            padding-top: 3px;
            margin:10px;
            text-align: center;
            font-size: 12px;
        }
        pre
        {
            margin: 0px 0px 10px 0px; 
            display: block; 
            background: #440208; 
            color: #f3f3f3; 
            font-family: Verdana; 
            border: 1px solid #9d0e0e; 
            padding: 5px; 
            font-size: 11px; 
            line-height: 14px;
            overflow:auto;
        }
    </style>
</head>
<body>
	<div id="container">
		<div class="header">{TITLE}</div>
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