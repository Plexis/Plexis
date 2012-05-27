<?php
// Turn off error reporting except errors
error_reporting( E_ERROR );

// Define a smaller Directory seperater and ROOT path
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

// Include our functions file
include(ROOT . DS .'installer'. DS .'includes'. DS .'functions.php');
include(ROOT . DS .'installer'. DS .'includes'. DS .'driver.php');

//Check to see if the installer is locked.
$installed = file_exists( "install.lock" );

// Get our step level
if(isset($_GET['step']))
{
    $step = $_GET['step'];
}
else
{
    $step = 1;
}
?>
<!DOCTYPE html>
<head>
	<title>Plexis CMS Installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<link rel="stylesheet" href="installer/css/main.css" type="text/css"/>
	<?php if( $installed ): ?>
		<script type="text/javascript" language="JavaScript">
			setTimeout( "back()", 5000 );
			
			function back() {
				history.go( -1 );
			}
		</script>
	<?php endif; ?>
</head>
<body>
	<div id="header">					
		<h1 id="title"><center><img src="installer/images/plexis.png" /></center></h1>
	</div>
	<div class="page">
		<div class="content">				
			<div class="content-header">
			<?php
				echo "<h4><center>Step ".$step."</center></h4>";
				echo "</div> <!-- .content-header -->";
				
				if( $installed ) //$installed = file_exists( "install.lock" );
				{
					show_error( "Plexis has already been installed! Please delete the install.lock file if you need to re-run the installer. You will be redirected in 5 seconds." );
				}
				else
				{
					// Include our steps file if one exists
					$file = ROOT . DS . 'installer'. DS .'includes'. DS .'step'.$step.'.php';
					if(file_exists($file)) include $file;

					// Include our view file
					include ROOT . DS . 'installer'. DS .'views'. DS .'step'.$step.'.php';
				}
            ?>
		</div> <!-- .content -->
	</div>
</body>
</html>