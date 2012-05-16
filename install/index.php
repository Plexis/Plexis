<?php
// Turn off error reporting except errors
error_reporting( E_ERROR );

// Define a smaller Directory seperater and ROOT path
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

// Include our functions file
include(ROOT . DS .'installer'. DS .'functions.php');
include(ROOT . DS .'installer'. DS .'Driver.php');

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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title>Plexis CMS Installer</title>
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

				if($step == 1)
				{
			?>		
					<!-- STEP 1 -->
					<form method="POST" action="index.php?step=2" class="form label-inline">
					<div class="main-content">		
						<p>
							Welcome to the Plexis Installer!. Before we start the installation proccess, we need to make sure your
							web server is compatible with the cms. Please select your realms emulator, and click the start at the bottom to begin.
						</p>
                        
                        <div class="field" >
                            <label for="user">Emulator: </label>
                            <select name="emulator">
                                <option value="trinity" select="selected">Trinity</option>
                                <option value="mangos" select="selected">Mangos</option>
								<option value="arcemu">ArcEmu</option>
                            </select>
                            <p class="field_help">Please select your emulator.</p>
                        </div>
        
						<div class="buttonrow-border">								
							<center><button><span>Start</span></button></center>			
						</div>
						<div class="clear"></div>
					</div> <!-- .main-content -->
					</form>
			<?php
				} 
				else
				{
                    // Include our step file
                    include( ROOT . DS . 'steps' . DS . $_POST['emulator'] . DS . 'step_'.$step.'.php');
                }
            ?>
		</div> <!-- .content -->
		<div id="footer">
			<center>
			<p>
				Template originally designed by <a href="http://rodcreative.com/">Rod Creative</a>, Modified by Wilson212 for his CMS projects.<br /> 
			</p>
			</center>
		</div>
	</div>
</body>
</html>