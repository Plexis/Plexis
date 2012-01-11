<div class="left-box">
    <h2>Registration</h2>
    <div class="left-box-content">
        <form method="post" action="{SITE_URL}/account/register" id="register-form">
            <input type="hidden" name="action" value="register" />
            <fieldset>
                <div class="div-center">
                
                    <label for="username">Username:</label> 
                    <input type="text" name="username" id="username" value="" size="30" tabindex="1" />
                    
                    <label for="password1">Password:</label> 
                    <input type="password" name="password1" id="password1" value="" size="30" tabindex="2" />
                    
                    <label for="password2">Repeat Password:</label> 
                    <input type="password" name="password2" id="password2" value="" size="30" tabindex="3" />
                    
                    <label for="email">Email:</label> 
                    <input type="text" name="email" id="email" value="" size="30" tabindex="4" />
                    
                    <br />
                    <label for="sq">Secret Question:</label> 
                    <select name="sq">{secret_questions}</select>
                    
                    <label for="sa">Secret Answer:</label> 
                    <input type="text" name="sa" id="sa" value="" size="30" tabindex="6" />
                    
                    <?php if( config('enable_captcha') == TRUE ): ?>
                        <br />
                        <center><img src="{SITE_URL}/account/captcha" alt="If you dont see an image, Contact an administrator." style="margin-left: -35px;"/></center>
                        <label for="captcha">Captcha:</label> 
                        <input type="text" name="captcha" id="captcha" value="" size="30" tabindex="7" />
                    <?php endif; ?>
                    <br />
                    <center><input type="submit" name="submit" value="Register" class="button" tabindex="8" /></center>
                </div>
            </fieldset>
        </form>
    </div>
</div>