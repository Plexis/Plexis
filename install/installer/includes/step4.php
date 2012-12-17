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
$connect = get_database_connections(true);
$DB = $connect['plexis'];

// Everthing should be fine, so first insert info into protected config file
$conffile = "../system/config/database.php";
$build = '';
$build .= "<?php
\$DB_configs = array(
    'PlexisDB' => array(
        'driver'	   => 'mysql',
        'host'         => '".$_POST['db_host']."',
        'port'         => '".$_POST['db_port']."',
        'username'     => '".$_POST['db_username']."',
        'password'     => '".$_POST['db_password']."',
        'database'     => '".$_POST['db_name']."'
    ),
    'RealmDB' => array(
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
$cmsconf = "../system/config/config.php";
if( is_writeable(  $cmsconf ) )
{
	$contents = file_get_contents( $cmsconf );
	$confhandle = fopen( $cmsconf, "wb" );
	
	$contents = preg_replace( "#(\\\$emulator\s*=\s*[\"|']{1}(.*?)[\"|']{1};)#i", "\$emulator = '". $_POST['emulator'] ."';", $contents );
	
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
    $install = $DB->run_sql_file("installer/sql/full_install.sql");
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