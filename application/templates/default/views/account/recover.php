<div class="left-box">
    <h2>Account Recovery Step 1</h2>
    <div class="left-box-content">
        <p>
            You forgot your password!? When we said "keep it secret, and keep it safe" we didn't mean for you to take it this far. For future reference, 
            forgetting your password completely is just a bit too secret to be practical. Well, no worries. We'll try to get it back for you. Start 
            by entering your Account Name and the Email that was used to create the account. If you've forgotten your Account Name or Registration Email 
            as well, You will need to contact an administrator.
        </p>
        <form method="post" action="{SITE_URL}/account/recover" id="recover-form">
            <input type="hidden" name="action" value="recover" />
            <input type="hidden" name="step" value="1" />
            <fieldset>
                <div class="div-center">
                    <label for="username">Account Username:</label> 
                    <input type="text" name="username" id="username" size="30" tabindex="1" />
                    
                    <label for="email">Registration Email:</label> 
                    <input type="text" name="email" id="email" size="30" tabindex="2" title="This is the email used when the account was created"/>
                    
                    <center><input type="submit" name="submit" value="Next Step" class="button" tabindex="3" /></center>
                </div>
            </fieldset>
        </form>
    </div>
</div>