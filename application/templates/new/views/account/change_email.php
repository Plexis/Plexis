<div class="left-box">
    <h2>Update Email</h2>
    <div class="left-box-content">
        <form method="post" action="{SITE_URL}/account/update/email">
            <input type="hidden" name="action" value="change-email" />
            <fieldset>
                <div class="div-center">
                    
                    <label for="password">Account Password:</label> 
                    <input type="password" name="password" id="password" value="" size="30" tabindex="1" />
                    
                    <label for="old-email">Old Email:</label> 
                    <input type="text" name="old-email" id="old-email" value="" size="30" tabindex="2" />
                    
                    <label for="new-email">New Email:</label> 
                    <input type="text" name="new-email" id="new-email" value="" size="30" tabindex="3" />
                    
                    <center><input type="submit" name="submit" value="Update Email" class="button" tabindex="4" /></center>
                </div>
            </fieldset>
        </form>
    </div>
</div>