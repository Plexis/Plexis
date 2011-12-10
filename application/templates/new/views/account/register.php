<div class="left-box">
    <h2>Registration</h2>
    <div class="left-box-content">
        <form method="post" action="{SITE_URL}/account/register">
            <input type="hidden" name="action" value="register" />
            <fieldset>
                <div class="div-center">
                
                    <label for="username">Username:</label> 
                    <input type="text" name="username" id="username" value="" size="30" tabindex="1" />
                    
                    <label for="password">Password:</label> 
                    <input type="password" name="password" id="password" value="" size="30" tabindex="2" />
                    
                    <label for="password2">Repeat Password:</label> 
                    <input type="password" name="password2" id="password2" value="" size="30" tabindex="3" />
                    
                    <label for="email">Email:</label> 
                    <input type="text" name="email" id="email" value="" size="30" tabindex="4" />
                    
                    <?php if( config('enable_captcha') == TRUE ): ?>
                        <center><img src="{SITE_URL}/account/captcha" alt="If you dont see an image, Then you have a problem!" /></center>
                        <label for="captcha">Captcha:</label> 
                        <input type="text" name="captcha" id="captcha" value="" size="30" tabindex="5" />
                    <?php endif; ?>
                    
                    <center><input type="submit" name="submit" value="Register" class="button" tabindex="6" /></center>
                </div>
            </fieldset>
        </form>
    </div>
</div>