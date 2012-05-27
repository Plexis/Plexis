<?php
// Initialize the no good tracker
$nogood = 0;

// PHP version check
if(strnatcmp(get_real_phpversion(),'5.3.0') >= 0)
{
    $phpver = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $phpver = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $nogood++;
} 

// Config Writable
if(is_writable('../application/config/config.php') == TRUE)
{
    $config_writable = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $config_writable = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $nogood++;
}

// Database config writable
if(is_writable('../application/config/database.config.php') == TRUE)
{
    $database_config_writable = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $database_config_writable = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $nogood++;
}

// Cache Writable
if(is_writable('../application/cache') == TRUE)
{
    $cache_writable = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $cache_writable = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $nogood++;
}

// Fsockopen check
if(function_exists("fsockopen")) 
{
    $fsock = "<img src='installer/images/check.png' height='18px' width='18px' />";
}
else
{
    $fsock = "<img src='installer/images/x.png' height='18px' width='18px' />";
    $nogood++;
}
?>