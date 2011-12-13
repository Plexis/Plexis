<div class="left-box">
    <h2>Account Recovery Step 1</h2>
    <div class="left-box-content">
        <p>
            You forgot your password!? When we said "keep it secret, and keep it safe" we didn't mean for you to take it this far. For future reference, 
            forgetting your password completely is just a bit too secret to be practical. Well, no worries. We'll try to get it back for you. Start 
            by entering your Account Name and Email into the box below. If you've forgotten your Account Name or Email as well, You will need to contact
            an administrator.
        </p>
        <form method="post" action="{SITE_URL}/account/recover">
            <input type="hidden" name="action" value="recover" />
            <input type="hidden" name="step" value="1" />
            <fieldset>
                <div class="div-center">
                    <label for="username">Account Username:</label> 
                    <input type="text" name="username" id="username" value="" size="30" tabindex="1" />
                    
                    <label for="email">Account Email:</label> 
                    <input type="text" name="email" id="email" value="" size="30" tabindex="2" />
                    
                    <center><input type="submit" name="submit" value="Next Step" class="button" tabindex="3" /></center>
                </div>
            </fieldset>
        </form>
    </div>
</div>