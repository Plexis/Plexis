<!DOCTYPE HTML>
<html>
<head>
    <!-- Title -->
    <title>Plexis Remote Debugger</title>
    
    <!-- Content type, And cache control -->
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
	<meta http-equiv="cache-control" content="no-cache"/>
	<meta http-equiv="expires" content="-1"/>
    
    <!-- Style Sheets -->
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Metrophobic" rel="stylesheet" type="text/css">
    
    <!-- Javascripts -->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/debug.js"></script>
    <script type="text/javascript" src="js/msgbox.js"></script>
    
    <!-- Robot Control -->
    <meta name="robots" content="noindex, nofollow"> 
</head>
<body>
    <div id="wrapper">
        <div class="block-border">
            <div class="block-header">
                <h1>Plexis Remote Debugger</h1>
                <div id="status">
                    <b>Status:</b> <b id="debug_status">None</b>
                </div>
            </div>
            <div class="block-content">
                <div id="title-bar">
                    <div id="waiting">
                        <a id="begin" href="#" class="button" style="float: right;">Begin Debugging</a>
                    </div>
                    <div id="started" style="display: none">
                        <a id="killScript" href="#" class="button red">Kill Script</a>
                        <a id="finishScript" href="#" class="button">Finish Script</a>
                        <a id="nextStep" href="#" class="button" style="float: right;">Next Step</a>
                        <a id="modifyVar" href="#" class="button" style="float: right;">Modify Variable</a>
                        <a id="getVar" href="#" class="button" style="float: right;">Get Variable</a>
                        <a id="getBacktrace" href="#" class="button" style="float: right;">Get Backtrace</a>
                    </div>
                </div>
            </div>
            <div class="block-content">
                <div id="ide">
                    <div id="ide_left">
                        <div id="trace_logs"></div>
                    </div>
                    <div id="ide_right">
                        <div id="code_window"></div>
                    </div>
                    <br style="clear: both;">
                </div>
            </div>
            <div class="block-content black">
                <div id="console"></div>
            </div>
        </div>
        <div class="spacer"><!-- SPACER --></div>
    </div>
</body>
</html>