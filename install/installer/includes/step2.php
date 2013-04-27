<?php
// Initialize the no good tracker
$can_continue = true;

// PHP version check
if( defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID >= 50300 )
{
    $phpver = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $phpver = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $can_continue = false;
}

//Check PDO
if( extension_loaded( "PDO" ) && ( extension_loaded( "pdo_mysql" ) || extension_loaded( "pdo_sqlite" ) ) )
{
    $pdo = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $pdo = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $can_continue = false;
}

//Check cURL
if( extension_loaded( "curl" ) )
{
    $curl = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $curl = "<img src='installer/images/warn.png' height='18px' width='18px' />";
}

//Check OpenSSL
if( extension_loaded( "openssl" ) )
{
    $https = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $https = "<img src='installer/images/warn.png' height='18px' width='18px' />";
}

//Check allow_url_fopen
if( ini_get( "allow_url_fopen" ) == 1 )
{
    $url_fopen = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $url_fopen = "<img src='installer/images/warn.png' height='18px' width='18px' />";
}

//Check GD
if( extension_loaded( "gd" ) )
{
    $gd = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $gd = "<img src='installer/images/warn.png' height='18px' width='18px' />";
}

// Config Writable
if(is_writable('../system/config/config.php') == TRUE)
{
    $config_writable = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $config_writable = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $can_continue = false;
}

// Database config writable
if(is_writable('../system/config/database.php') == TRUE)
{
    $database_config_writable = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $database_config_writable = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $can_continue = false;
}

// Cache Writable
if(is_writable('../system/cache') == TRUE)
{
    $cache_writable = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $cache_writable = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $can_continue = false;
}

// Fsockopen check
if(function_exists("fsockopen"))
{
    $fsock = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $fsock = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $can_continue = false;
}
?>