<div class="left-box">
    <h2>Change Password</h2>
    <div class="left-box-content">
        <form method="post" action="{SITE_URL}/account/update/password" id="password-form">
            <input type="hidden" name="action" value="change-password" />
            <fieldset>
                <div class="div-center">
                    
                    <label for="old_password">Current Password:</label> 
                    <input type="password" name="old_password" id="old_password" value="" size="30" tabindex="1" />
                    
                    <label for="password1">New Password:</label> 
                    <input type="password" name="password1" id="password1" value="" size="30" tabindex="2" />
                    
                    <label for="password2">Repeat New Password:</label> 
                    <input type="password" name="password2" id="password2" value="" size="30" tabindex="3" />
                    
                    <center><input type="submit" name="submit" value="Update Password" class="button" tabindex="5" /></center>
                </div>
            </fieldset>
        </form>
    </div>
</div>