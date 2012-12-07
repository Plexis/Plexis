<?php
// Turn off error reporting except fatal errors
error_reporting( E_ERROR );

// Define a smaller Directory seperater and ROOT path
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

// Include our functions file
include(ROOT . DS .'installer'. DS .'includes'. DS .'functions.php');
include(ROOT . DS .'installer'. DS .'includes'. DS .'driver.php');

// Get our step level
$step = (isset($_GET['step'])) ? $_GET['step'] : 1;
?>
<!DOCTYPE html>
<html>
<head>
	<title>Plexis CMS Installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<link rel="stylesheet" href="installer/css/main.css" type="text/css"/>
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
				
				if( file_exists( "install.lock" ) )
				{
					show_error( "Plexis has already been installed! Please delete the install.lock file if you need to re-run the installer. You will be redirected in 5 seconds." );
					
					//build the site url
					$host = $_SERVER["HTTP_HOST"];
					$self = rtrim( dirname( filter_var( $_SERVER["PHP_SELF"], FILTER_SANITIZE_STRING ) ), "install" );
					$protocol = ( isset( $_SERVER["HTTPS"] ) ) ? "https://" : "http://";
					
					header( "Refresh:5;url=" . $protocol . $host . $self );
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