<form action='{BASE_URL}/account/login' method='POST'>
    <input type="hidden" name="action" value="register" />
	<div style="margin:1px;padding:6px 9px 6px 9px;text-align:center;">
		<b>Username: </b> <input type="text" size="26" style="font-size:11px;" name="username">
	</div>
	<div style="margin:1px;padding:6px 9px 6px 9px;text-align:center;">
		<b>Password: </b> <input type="password" size="26" style="font-size:11px;" name="password">
	</div>
	<div style="margin:1px;padding:6px 9px 6px 9px;text-align:center;">
		<b>Email: </b> <input type="text" size="32" style="font-size:11px;" name="email">
	</div>
	<div style="margin:1px;padding:6px 9px 0px 9px;text-align:center;">
		<input type="submit" size="16" class="button" style="font-size:12px;" value="Register">
	</div>
</form>