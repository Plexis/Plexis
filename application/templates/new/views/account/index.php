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
    <h2>Change Password</h2>
    <div class="left-box-content">
        <form method="post" action="{SITE_URL}/account/">
            <input type="hidden" name="action" value="update" />
            <fieldset>
                <div class="div-center">
                    
                    <label for="password">Old Password:</label> 
                    <input type="password" name="password" id="password" value="" size="30" tabindex="1" />
                    
                    <label for="password2">New Password:</label> 
                    <input type="password" name="password2" id="password2" value="" size="30" tabindex="2" />
                    
                    <label for="password3">Repeat New Password:</label> 
                    <input type="password" name="password3" id="password3" value="" size="30" tabindex="3" />
                    
                    <center><input type="submit" name="submit" value="Update Account" class="button" tabindex="5" /></center>
                </div>
            </fieldset>
        </form>
    </div>
</div>

<div class="left-box">
    <h2>Update Email</h2>
    <div class="left-box-content">
        <form method="post" action="{SITE_URL}/account/">
            <input type="hidden" name="action" value="update" />
            <fieldset>
                <div class="div-center">
                    
                    <label for="password">Account Password:</label> 
                    <input type="password" name="password" id="password" value="" size="30" tabindex="6" />
                    
                    <label for="email">Old Email:</label> 
                    <input type="text" name="email" id="email" value="" size="30" tabindex="7" />
                    
                    <label for="email">New Email:</label> 
                    <input type="text" name="email" id="email" value="" size="30" tabindex="8" />
                    
                    <center><input type="submit" name="submit" value="Update Email" class="button" tabindex="9" /></center>
                </div>
            </fieldset>
        </form>
    </div>
</div>