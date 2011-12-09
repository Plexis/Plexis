<div class="left-box">
    <h2>Registration</h2>
    <div class="left-box-content">
        <form method="post" action="{SITE_URL}/account/register" id="form">
            <input type="hidden" name="action" value="register" />
            <fieldset>	
                <label for="username">Username</label> <input type="text" name="username" id="username" value="" tabindex="1" />
                <label for="password">Password</label> <input type="password" name="password" id="password" value="" tabindex="2" />
                <label for="email">Email</label> <input type="text" name="email" id="email" value="" tabindex="3" />
                <input type="submit" name="submit" value="Register" class="send" tabindex="4" />
            </fieldset>
        </form>
    </div>
</div>