<form method="post" action="{SITE_URL}/account/register">
    <input type="hidden" name="action" value="register" />
	<label for="username">Username: </label>
	<input id="username" type="text" value="" name="username" tabindex="1" />

	<label for="password">Password:</label>                       
	<input id="password" type="password" value="" name="password" tabindex="2" />
	
	<label for="email">Email: </label>
	<input id="email" type="text" value="" name="email" tabindex="3" /> 
	
	<input type="submit" id="submit" value="Send" name="submit" tabindex="4"/>	
</form> 