<!-- STEP 2 -->
<form method="POST" action="index.php?step=3" class="form label-inline">
<input type="hidden" name="emulator" value="<?php echo $_POST['emulator']; ?>" />
<div class="main-content">
    <p>
        If you see any red X's here, then your server is not compatible to run Plexis. Any and all X's can be fixed by
        contacting your webhost.<br /><br />
        <table>
            <tr>
                <td>
                    PHP Version 5.3 or newer <?php echo " (". get_real_phpversion() .")"; ?><br />
                    <span style="color: #999; font-style: italic;">A minimum version of PHP 5.3.0 is required to run Plexis CMS</span>
                </td>
                <td><?php echo $phpver; ?></td>
            </tr>
            <tr>
                <td>
                    PHP PDO Extension (PDO with MySQL or PDO with SQLite)<br />
                    <span style="color: #999; font-style: italic;">PDO is required for all database operations, Plexis cannot run without it.</span>
                </td>
                <td><?php echo $pdo; ?></td>
            </tr>
            <tr>
                <td>
                    cURL Extension<br />
                    <span style="color: #999; font-style: italic;">cURL is recommended but not required, but some features may be unavailable without it.</span>
                </td>
                <td><?php echo $curl; ?></td>
            </tr>
            <tr>
                <td>
                    Open SSL and HTTPS Stream Wrappers<br />
                    <span style="color: #999; font-style: italic;">Open SSL and is not required, but some features may be unavailable without it.</span>
                </td>
                <td><?php echo $https; ?></td>
            </tr>
            <tr>
                <td>
                    allow_url_fopen<br />
                    <span style="color: #999; font-style: italic;">The allow_url_fopen INI directive may cause some features to be unavailable if not enabled.</span>
                </td>
                <td><?php echo $url_fopen; ?></td>
            </tr>
            <tr>
                <td>
                    PHP GD Library<br />
                    <span style="color: #999; font-style: italic;">PHP GD is recommended by not required, CAPTCHAs will not be available without it.</span>
                </td>
                <td><?php echo $gd; ?></td>
            </tr>
            <tr>
                <td>
                    Config.php Writable by Webserver<br />
                    <span style="color: #999; font-style: italic;">chmod config.php to 777 if this check fails.</span>
                </td>
                <td><?php echo $config_writable; ?></td>
            </tr>
            <tr>
                <td>
                    Database.Config.php Writable by Webserver<br />
                    <span style="color: #999; font-style: italic;">chmod database.config.php to 777 if this check fails.</span>
                </td>
                <td><?php echo $database_config_writable; ?></td>
            </tr>
            <tr>
                <td>
                    Cache ("system/cache/") Writable by Webserver<br />
                    <span style="color: #999; font-style: italic;">chmod system/cache/ to 777 if this check fails.</span>
                </td>
                <td><?php echo $cache_writable; ?></td>
            </tr>
            <tr>
                <td>
                    Fsockopen Enabled<br />
                    <span style="color: #999; font-style: italic;">The fsockopen() function is required for Plexis to run properly.</span>
                </td>
                <td><?php echo $fsock; ?></td>
            </tr>
        </table>
    </p>
    <div class="buttonrow-border">
        <?php
            if( $can_continue )
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