<?php
/*
| ---------------------------------------------------------------
| Function: fetch_account()
| ---------------------------------------------------------------
|
| Fetches an account id, username, and email from the realm DB
|
| @Param: $name - The username of the account
| @Return: (Array | false)
|
*/	
function fetch_account($name)
{
	global $Realm;
	return $Realm->query("SELECT `id`, `login` as `username`, `email` FROM `accounts` WHERE `login` LIKE ? LIMIT 1", array($name))->fetch_row();
}

/*
| ---------------------------------------------------------------
| Function: create_account()
| ---------------------------------------------------------------
|
| Creates an account in the realm database
|
| @Param: $name - The username of the account
| @Param: $pass - The password of the account
| @Return: (Bool) True on success, false otherwise
|
*/		
function create_account($username, $pass)
{
	global $Realm;
	$password = sha1($username .':'. $pass);
	$result = $Realm->insert('accounts', array('login' => $username, 'password' => $pass, 'encrypted_password' => $password));
	return ($result !== false) ? true : false;
}
?>