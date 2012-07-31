<?php
/* 
| -------------------------------------------------------------- 
| Configuration
| --------------------------------------------------------------
*/

$emulator = 'trinity';  // Please select 'trinity', 'mangos', or 'arcemu'
$accountId = 5;         // Select a known account ID in your database
$charId = 1;            // Enter a character ID that exists in your character DB

// Auth / Login Database Server Connection
$connA = array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'port'      => '3306',
    'username'  => 'admin',
    'password'  => 'admin',
    'database'  => 'auth'
);
// Character Database Server Connection
$connC = array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'port'      => '3306',
    'username'  => 'admin',
    'password'  => 'admin',
    'database'  => 'characters'
);

// === End Configs... Dont edit anything below this line === //

// For benchmarking purposes
$start = microtime(1);


/* 
| -------------------------------------------------------------- 
| This example shows you how to load a realm, and fetch an account
| --------------------------------------------------------------
*/

// Include the wowlib class
include 'Wowlib.php';

// Init the wowlib
Wowlib::Init($emulator);

// Fetch realm, and Dump the account id of 5
$Realm = Wowlib::getRealm('myrealmid', $connA);
$Account = $Realm->fetchAccount( $accountId );
echo "This account username for account id $accountId is ". $Account->getUsername();

/* 
| -------------------------------------------------------------- 
| In this next example, Lets fetch a character
| --------------------------------------------------------------
*/

// Load a driver first, which ever is most compatible with your core revision
// We will skip the world data connection by placing null as the 3rd param
$Driver = Wowlib::load('_default', $connC, null);

// The driver... Each method under the driver object, is a class that is loacated
// inside the loaded driver folder "drivers/$emulator/_default" in this case
$Character = $Driver->characters->fetch( 2 );
if(is_object($Character))
{
    echo "<br /><br />This character's name is ". $Character->getName() ." and he is level ". $Character->getLevel() ."!";
}
else
{
    echo "<br /><br />Character Doesnt Exist :O";
}

// Echo benchmark time
echo "<br /><br />Rendered in ". round(microtime(1) - $start, 4) ." seconds" ;
?>