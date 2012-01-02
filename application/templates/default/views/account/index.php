<div class="left-box">
    <h2>Account Information</h2>
    <div class="left-box-content">
        <center>
            Your Username is: {session.user.username}<br />
            Your Account ID is: {session.user.id}<br />
            Account Level: {session.user.title}<br />
            Join Date:  {joindate}<br />
            Current IP: <?php echo $_SERVER['REMOTE_ADDR']; ?><br />
            Registration IP: {session.user.registration_ip}<br /><br />
        </center>
    </div>
</div>

<div class="left-box">
    <h2>Account Options</h2>
    <div class="left-box-content">
        <center>
            <a href="{SITE_URL}/account/update/password" class="button">Change Password</a>
            <a href="{SITE_URL}/account/update/email" class="button">Change Email</a>
			<a href="{SITE_URL}/account/invite_keys" class="button">Invitation Keys</a>
			<!--This is really just for aesthetics.-->
			<div style="height: 11px; width: 0px; background: transparent;"></div>
        </center>
    </div>
</div>