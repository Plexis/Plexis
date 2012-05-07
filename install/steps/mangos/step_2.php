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
<!-- STEP 2 -->
<form method="POST" action="index.php?step=3" class="form label-inline">
<input type="hidden" name="emulator" value="<?php echo $_POST['emulator']; ?>" />
<div class="main-content">		
    <p>
        If you see any red X's here, then your server is not compatible to run Plexis. Any and all X's can be fixed by
        contacting your webhost.<br /><br />
        <table>
            <tr>
                <td>PHP Version 5.3 or newer <?php echo " (". get_real_phpversion() .")"; ?> </td>
                <td><?php echo $phpver; ?></td>
            </tr>
            <tr>
                <td>Config.php Writable by Webserver   </td>
                <td><?php echo $config_writable; ?></td>
            </tr>
            <tr>
                <td>Database.Config.php Writable by Webserver   </td>
                <td><?php echo $database_config_writable; ?></td>
            </tr>
            <tr>
                <td>Cache ("application/cache/") Writable by Webserver </td>
                <td><?php echo $cache_writable; ?></td>
            </tr>
            <tr>
                <td>Fsockopen Enabled </td>
                <td><?php echo $fsock; ?></td>
            </tr>
        </table>
    </p>
    <div class="buttonrow-border">
        <?php
            if($nogood == 0)
            { 
                echo "<center><button><span>Continue to step 3</span></button></center>";
            }
            else
            {
                echo "<center><font color='red'> Sorry, You Cannot Go To Step 3. </font></center>";
            }
        ?>
    </div>
    <div class="clear"></div>
</div> <!-- .main-content -->
</form>