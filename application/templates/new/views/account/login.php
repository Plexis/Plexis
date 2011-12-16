<div class="left-box">
    <h2>Login</h2>
    <div class="left-box-content">
        <form method="post" action="{SITE_URL}/account/login" id="login-form">		
            <fieldset>
                <div class="div-center">
                    <label for="username">Username:</label> <input type="text" name="username" id="username" size="30" value="" tabindex="1" />
                    <label for="password">Password:</label> <input type="password" name="password" id="password" size="30" value="" tabindex="2" />
                    <center>
                        <input type="submit" name="submit" value="Login" class="button" tabindex="3" /> <br />
                        <small><a href="{SITE_URL}/account/recover">Recover lost password</a></small>
                    </center>
                </div>
            </fieldset>
        </form>
    </div>
</div>
