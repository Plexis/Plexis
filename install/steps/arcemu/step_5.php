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
$connect = get_database_connections();
$DB = $connect['plexis'];
$Realm = $connect['realm'];

$account = $Realm->query("SELECT * FROM `accounts` WHERE `login` LIKE ?", array($_POST['account']))->fetch_row();
if($account != FALSE) 
{
    $array = array(
        'id' => $account['acct'],
        'username' => $account['login'],
        'email' => $account['email'],
        'registration_ip' => $_SERVER['REMOTE_ADDR'],
        'activated' => 1,
        'group_id' => 4
    );
    $return = $DB->insert('pcms_accounts', $array);
}
else 
{
    // No such account, creating one, in this case pwd is needed, so checking whether it's provided...
    $password = sha_password($_POST['account'], $_POST['pass']);
    $result = $Realm->insert('accounts', array('login' => $_POST['account'], 'password' => $_POST['pass'], 'encrypted_password' => $password));
    if($result !== FALSE)
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
        $result = $DB->insert('pcms_accounts', $ac);
        $return = 2;
    }
    else
    {
        $return = 0;
    }
}
?>
<div class="main-content">		
<p>
    <?php if($return > 0)
    { ?>
        Congratulations! Plexis is installed and ready for use! Please login and visit the admin panel to further configure the site!
        Also, you need to <u><b>DELETE the install folder! ("install/")</b></u> to prevent users from 
        hacking your site. <font color="red">You cannot visit the site till the install folder is deleted, </font>
        <br /><br /><a href="../index.php">Click Here</a> To go to your Plexis home page.
    <?php
    } ?>
</p>
</div>