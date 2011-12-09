<div class="left-box">
    <h2>Registration</h2>
    <div class="left-box-content">
        <form method="post" action="{SITE_URL}/account/register" id="form">
            <input type="hidden" name="action" value="register" />
            <fieldset>	
                <label for="username">Username</label> <input type="text" name="username" id="username" value="" tabindex="1" />
                <label for="password">Password</label> <input type="password" name="password" id="password" value="" tabindex="2" />
                <label for="email">Email</label> <input type="text" name="email" id="email" value="" tabindex="3" />
                <?php if( config('enable_captcha') == TRUE ): ?>
                    <img src="{SITE_URL}/account/captcha" alt="If you dont see an image, Then you have a problem!" />
                    <label for="captcha">Enter the Captcha:</label> <input type="text" name="captcha" id="captcha" value="" tabindex="4" />
                <?php endif; ?>
                <input type="submit" name="submit" value="Register" class="send" tabindex="5" />
            </fieldset>
        </form>
    </div>
</div>