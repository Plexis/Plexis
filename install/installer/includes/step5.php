<?php
if($_POST['pass'] != $_POST['pass2'])
{
    die('<div class="alert error">Passwords dont match!. Please <a href="javascript: history.go(-1)">go back</a> and correct it.</div>');
}
if(empty($_POST['account']))
{
    show_error('No account name was given. Please <a href="javascript: history.go(-1)">go back</a> and correct it.');
    die();
}

// Make sure username is correct length
if(strlen($_POST['account']) < 4)
{
    show_error('Account name needs to be at least 4 characters long.');
    die();
}

// Make sure password is correct length
if(strlen($_POST['pass']) < 4)
{
    show_error('Password needs to be at least 4 characters long.');
    die();
}

// == Check DB connections first! == //
$connect = get_database_connections(false);
$DB = $connect['plexis'];
$Realm = $connect['realm'];

// == Include emulator file == //
include ROOT . DS . 'emulators' . DS . $_POST['emulator'] .'.php';

// Fetch posted account name
$account = fetch_account($_POST['account']);
if($account != FALSE) 
{
    $array = array(
        'id' => $account['id'],
        'username' => $account['username'],
        'email' => $account['email'],
        'registration_ip' => $_SERVER['REMOTE_ADDR'],
        'activated' => 1,
        'group_id' => 4
    );
	$mode = 1;
    $success = ($DB->insert('pcms_accounts', $array, true) != false) ? true : false;
}
else 
{
    // No such account, creating one, in this case pwd is needed, so checking whether it's provided...
    $result = create_account($_POST['account'], $_POST['pass']);
    if($result != FALSE)
    {
        // Get the account ID
        $accountid = $Realm->last_insert_id();
        
        // Connect to the Plexis DB
        $ac = array(
            'id' => $accountid, 
            'username' => $_POST['account'], 
            'activated' => 1, 
            'registration_ip' => $_SERVER['REMOTE_ADDR'], 
            'group_id' => 4
        );
        $success = ($DB->insert('pcms_accounts', $ac, true) != false) ? true : false;
        $mode = 2;
    }
    else
    {
		$success = false;
        $mode = 0;
    }
}

// Lock the installer on success
if($success == true || $mode > 0)
{
	$file = fopen( "install.lock", "w+" );
	fclose( $file );
}
?>