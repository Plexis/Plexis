<?php
// Check if everything is given for Plexis DB
if (!$_POST['db_host'] | !$_POST['db_port'] | !$_POST['db_username'] | !$_POST['db_password'] | !$_POST['db_name']) 
{
    show_error('One or more fields are blank. Please <a href="javascript: history.go(-1)">Go Back</a> and correct it.');
    die();
}

// Check if everything is given for realm DB
if (!$_POST['rdb_host'] | !$_POST['rdb_port'] | !$_POST['rdb_username'] | !$_POST['rdb_password'] | !$_POST['rdb_name']) 
{
    show_error('One or more fields are blank. Please <a href="javascript: history.go(-1)">Go Back</a> and correct it.');
    die();
}

// == Check DB connections first! == //
$connect = get_database_connections();
$DB = $connect['plexis'];

// Everthing should be fine, so first insert info into protected config file
$conffile = "../application/config/database.config.php";
$build = '';
$build .= "<?php
\$DB_configs = array(
    'DB' => array(
        'driver'	   => 'mysql',
        'host'         => '".$_POST['db_host']."',
        'port'         => '".$_POST['db_port']."',
        'username'     => '".$_POST['db_username']."',
        'password'     => '".$_POST['db_password']."',
        'database'     => '".$_POST['db_name']."'
    ),
    'RDB' => array(
        'driver'	   => 'mysql',
        'host'         => '".$_POST['rdb_host']."',
        'port'         => '".$_POST['rdb_port']."',
        'username'     => '".$_POST['rdb_username']."',
        'password'     => '".$_POST['rdb_password']."',
        'database'     => '".$_POST['rdb_name']."'
    )
);
?>";
// Make sure the  config is still writable
if (is_writeable($conffile))
{
    $openconf = fopen($conffile, 'wb');
    fwrite($openconf, $build);
    fclose($openconf);
}
else 
{ 
    show_error('Couldn\'t open database.config.php for editing, it must be writable by webserver! Please set your premissions and try again.');
    die();
}

// Edit the config.php file and switch it to the Trinity emulator (Thanks Skye :p )
$cmsconf = "../application/config/config.php";
if( is_writeable(  $cmsconf ) )
{
	$contents = file_get_contents( $cmsconf );
	$confhandle = fopen( $cmsconf, "wb" );
	
	$contents = preg_replace( "#(\\\$emulator\s*=\s*[\"|']{1}(.*?)[\"|']{1};)#i", "\$emulator = \"trinity\";", $contents );
	
	fwrite( $confhandle, $contents );
	fclose( $confhandle );
}
else
{
	show_error( "Couldn\'t open config.php for editing, it must be writable by webserver! Please set your permissions and try again." );
	die();
}

// Preparing for sql injection... (prashing, etc...)
if(!isset($_POST['skip']))
{
    // Dealing with the full install sql file
    $install = $DB->run_sql_file("../application/assets/sql/full_install.sql");
    if($install)
    {
        output_message('success', 'Plexis database installed');
    }
    else
    {
        output_message('error', 'There was an error installing the Plexis DB');
    }
}
?>
<!-- STEP 4 -->
<form method="POST" action="index.php?step=5" class="form label-inline">
    <input type="hidden" name="db_host" value="<?php echo $_POST['db_host']; ?>">
    <input type="hidden" name="db_port" value="<?php echo $_POST['db_port']; ?>">
    <input type="hidden" name="db_name" value="<?php echo $_POST['db_name']; ?>">
    <input type="hidden" name="db_username" value="<?php echo $_POST['db_username']; ?>">
    <input type="hidden" name="db_password" value="<?php echo $_POST['db_password']; ?>">
    <input type="hidden" name="rdb_host" value="<?php echo $_POST['rdb_host']; ?>">
    <input type="hidden" name="rdb_port" value="<?php echo $_POST['rdb_port']; ?>">
    <input type="hidden" name="rdb_name" value="<?php echo $_POST['rdb_name']; ?>">
    <input type="hidden" name="rdb_username" value="<?php echo $_POST['rdb_username']; ?>">
    <input type="hidden" name="rdb_password" value="<?php echo $_POST['rdb_password']; ?>">
    <input type="hidden" name="emulator" value="<?php echo $_POST['emulator']; ?>" />

    <div class="main-content">		
        <p>
            Please create an admin account. If you already have an account, then type in your info to log in, so 
            you can be added as a site admin.
        </p>
        
        <div class="field">
            <label for="user">Username: </label>
            <input id="user" name="account" size="20" type="text" class="medium"/>
        </div>
        
        <div class="field">
            <label for="pass">Password: </label>
            <input id="pass" name="pass" size="20" type="password" class="medium"/>
        </div>
        
        <div class="field">
            <label for="pass2">Repeat Password: </label>
            <input id="pass2" name="pass2" size="20" type="password" class="medium"/>
        </div>
        
        <div class="buttonrow-border">								
            <center><button><span>Submit</span></button></center>			
        </div>
        <div class="clear"></div>
    </div> <!-- .main-content -->
</form>