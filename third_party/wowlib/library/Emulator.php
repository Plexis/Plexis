<?php
/* 
| --------------------------------------------------------------
| 
| WowLib Framework for WoW Private Server CMS'
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/
namespace Wowlib;

class Emulator implements iEmulator
{
    // Our DB Connection
    protected $DB;
    
    // Emulator
    protected $emulator = '';
    
    // Array of found extensions
    protected $ext = array();
    
    // Array of options
    protected $queryConfig = array(
        'limit' => 50,
        'offset' => 0,
        'where' => '',
        'bind' => array(),
        'orderby' => '',
        'direction' => 'ASC'
    );
    
    // Our emulator driver and loaded extensions
    protected $config;
    protected $loaded = array();
    
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($emu, $DB)
    {
        // Set local variables
        $this->DB = $DB;
        $this->emulator = $emu;
        
        // First, we must load the emulator config
		$file = path( WOWLIB_ROOT, 'library', $emu, 'Driver.php' );
        
        // If extension doesnt exist, return false
        if( !file_exists( $file ) ) throw new \Exception('Config file for emulator '. $emu .' not found');
        require $file;
        
        // Set config array
        $this->config = $config;
        
        // Load our extensions needed
        require_once path( WOWLIB_ROOT, 'library', 'Account.php' );
        require_once path( WOWLIB_ROOT, 'library', 'Realm.php' );
        
        // load additional extensions
        $afile = path( WOWLIB_ROOT, 'library', $emu, 'Account.php' );
        $rfile = path( WOWLIB_ROOT, 'library', $emu, 'Realm.php' );
        if(file_exists($afile) && !class_exists("\\Wowlib\\$emu\\Account", false)) require $afile;
        if(file_exists($rfile) && !class_exists("\\Wowlib\\$emu\\Realm", false)) require $rfile;
    }
    
/*
| ---------------------------------------------------------------
| Method: fetchAccount()
| ---------------------------------------------------------------
|
| This function queries the accounts table and pulls all the users
|   information into an object
|
| @Param: (Int | String) $id - The account ID or Username we are loading
| @Return (Object) - returns the account object
|
*/
    public function fetchAccount($id)
    {
        // Build config
        $config = $this->queryConfig;
        $col = (is_int($id)) ? $this->config['accountColumns']['id'] : $this->config['accountColumns']['username'];
        $config['where'] = "`{$col}`='{$id}'";
        
        // Grab Realms
        $query = $this->_buildQuery('A', $config);
        $row = $this->DB->query( $query )->fetchRow();
        if(!is_array($row)) return false;
        
        // Get our classname
        $class = "Wowlib\\{$this->emulator}\\Account";
        if(!class_exists($class, false)) $class = "Wowlib\\Account";
        
        // Try to load the class
        try {
            $account = new $class($row, $this);
        }
        catch(\Exception $e) {
            $account = false;
        }
        return $account;
    }
    
/*
| ---------------------------------------------------------------
| Method: getAccountList
| ---------------------------------------------------------------
|
| This method is used to list all the accounts from the accounts
| table.
|
| @Param: (Array) An array of query configurations (See Documentation)
| @Retrun: (Array): An array of accounts
|
*/
    public function getAccountList($config = array())
    {
        // Merge Configs
        $config = array_merge($this->queryConfig, $config); 
        
        // Get our query prepared into a statement
        $statement = $this->prepare('A', $config);
        
        // Execute the statement
        $statement->execute();
        $online = array();
        
        // Get our classname
        $class = "Wowlib\\{$this->emulator}\\Account";
        if(!class_exists($class, false)) $class = "Wowlib\\Account";
        
        // Build an array of character objects
        while($row = $statement->fetch())
        {
            $online[] = new $class($row, $this);
        }
        
        return $online;
    }
    
/*
| ---------------------------------------------------------------
| Method: fetchRealm()
| ---------------------------------------------------------------
|
| This function gets the realm ID into an object
|
| @Param: (Int) $id - The realm ID we are requesting the information from
| @Return (Object) - Returns the RealmId Object, or false on failure
|
*/
    public function fetchRealm($id)
    {
        // Make sure this emulator support realmlists!
        if(!$this->config['realmTable']) return array();
        
        // Build config
        $config = $this->queryConfig;
        $col = $this->config['realmColumns']['id'];
        $config['where'] = "`{$col}`={$id}";
        
        // Grab Realms
        $query = $this->_buildQuery('R', $config);
        $row = $this->DB->query( $query )->fetchRow();
        if(!is_array($row)) return false;
        
        // Get our classname
        $class = "Wowlib\\{$this->emulator}\\Realm";
        if(!class_exists($class, false)) $class = "Wowlib\\Realm";
        
        // Try and build the realm object
        try {
            $realm = new $class($row, $this);
        }
        catch (\Exception $e) {
            $realm = false;
        }
        return $realm;
    }
    
/*
| ---------------------------------------------------------------
| Method: realmlist()
| ---------------------------------------------------------------
|
| This function gets the realmlist from the database
|
| @Return (Array) - Returns an array of Realm objects
|
*/
    public function getRealmlist($config = array())
    {
        // Make sure this emulator support realmlists!
        if(!$this->config['realmTable']) return array();
        
        // Merge Configs
        $config = array_merge($this->queryConfig, $config);
        
        // Sort by ID by default
        if($config['orderby'] == '') 
            $config['orderby'] = $this->config['realmColumns']['id'];
        
        // Get our query prepared into a statement
        $statement = $this->prepare('R', $config);
        
        // Execute the statement
        $statement->execute();
        
        // Get our classname
        $class = "Wowlib\\{$this->emulator}\\Realm";
        if(!class_exists($class, false)) $class = "Wowlib\\Realm";
        
        // Build the array of realm objects
        $realms = array();
        while($row = $statement->fetch())
        {
            $realms[] = new $class($row, $this);
        }
        
        return $realms;
    }
    
/*
| ---------------------------------------------------------------
| Method: uptime()
| ---------------------------------------------------------------
|
| This function gets the realms $id uptime
|
| @Param: (Int) $id - The realm ID we are requesting the information from
| @Return (Int) Time string of FALSE if unavailable
|
*/
    public function uptime($id)
    {
        // Get our table and column names
        $table = $this->config['uptimeTable'];
        $rid = $this->config['uptimeColumns']['realmId'];
        $cid = $this->config['uptimeColumns']['startTime'];
        if($table == false) return false;
        
        // Grab Realms
        $query = "SELECT MAX(`{$cid}`) FROM `{$table}` WHERE `{$rid}`=?";
        $result = $this->DB->query( $query, array($id) )->fetchColumn();
        return (time() - $result);
    }
    
/*
| ---------------------------------------------------------------
| Method: createAccount()
| ---------------------------------------------------------------
|
| This function creates an account using the provided username
|   and password.
|
| @Param: (String) $username - The account username
| @Param: (String) $password - The new account (unencrypted) password
| @Param: (String) $email - The new account email
| @Param: (String) $ip - The Registeree's IP address
| @Return (Mixed) - Returns the new Account ID on success, FALSE otherwise
|
*/
    public function createAccount($username, $password, $email = NULL, $ip = '0.0.0.0')
    {
        // Make sure the username doesnt exist, just incase the script didnt check yet!
        if($this->accountExists($username)) return false;
        
        // SHA1 the password
        $user = strtoupper($username);
        $pass = strtoupper($password);
        $shap = sha1($user.':'.$pass);
        
        // Get our column names
        $cols = $this->config['accountColumns'];
        
        // Build our tables and values for Database insertion
        $data = array(
            "{$cols['username']}" => $username, 
            "{$cols['email']}" => $email, 
        );
        
        // Condition based columns
        if($cols['password']) $data[ $cols['password'] ] = $password;
        if($cols['shaPassword']) $data[ $cols['shaPassword'] ] = $shap;
        if($cols['lastIp']) $data[ $cols['laspIp'] ] = $ip;
        
        // Insert into the database
        $this->DB->insert("account", $data);
        
        // If we have an affected row, then we return TRUE
        return ($this->DB->numRows() > 0) ? $this->DB->lastInsertId() : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: validate()
| ---------------------------------------------------------------
|
| This method takes a username and password, and logins in with
|   that information. If the password matches the pasword in the
|   database, we return the account id. Else we return FALSE,
|
| @Param: (String) $username - The account username
| @Param: (String) $password - The account (unencrypted) password
| @Return (Bool) - Returns TRUE on success, FALSE otherwise
|
*/
    public function validate($username, $password)
    {
        // Get our table and column names
        $table = $this->config['accountTable'];
        $cols = $this->config['accountColumns'];
        $passcol = ($cols['shaPassword'] != false) ? $cols['shaPassword'] : $cols['password'];
        
        // Load the users info from the Realm DB
        $query = "SELECT `{$cols['id']}` AS `id`, `{$passcol}` AS `password` FROM `{$table}` WHERE `{$cols['username']}`=?";
        $result = $this->DB->query( $query, array($username) )->fetchRow();
        
        // Make sure the username exists!
        if(!is_array($result)) return false;
        
        // SHA1 the password check
        if($cols['shaPassword'] != false)
        {
            $user = strtoupper($username);
            $pass = strtoupper($password);
            $password = sha1($user.':'.$pass);
            
            // If the result was false, then username is no good. Also match passwords.
            return ( strtolower($result['password']) == $password ) ? (int) $result['id'] : false;
        }
        
        return ( $result['password'] == $password ) ? true : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: login()
| ---------------------------------------------------------------
|
| This method takes a username and password, and logins in with
|   that information. If the password matches the pasword in the
|   database, we return the Account Object. Else we return FALSE,
|
| @Param: (String) $username - The account username
| @Param: (String) $password - The account (unencrypted) password
| @Return (Bool | Int) - Returns and object on success, false
|   if the account doesnt exist, and 0 if the password was incorrect
|
*/
    public function login($username, $password)
    {
        // Get our table and column names
        $table = $this->config['accountTable'];
        $columns = $this->config['accountColumns'];
        $passcol = ($cols['shaPassword'] != false) ? $cols['shaPassword'] : $cols['password'];
        
        // Prepare the column names
        $cols = "`". implode('`, `', $columns) ."`";
        
        // Load the users info from the Realm DB
        $query = "SELECT {$cols} FROM `{$table}` WHERE `{$columns['username']}`=?";
        $result = $this->DB->query( $query, array($username) )->fetchRow();
        if(!is_array($result)) return false;
        
        // SHA1 the password check
        if($cols['shaPassword'] != false)
        {
            $user = strtoupper($username);
            $pass = strtoupper($password);
            $password = sha1($user.':'.$pass);
            
            // Match the SHA passwords
            if( strtolower($result[$passcol]) == $password ) goto Account;
        }
        else
        {
            if( $result[$passcol] == $password ) goto Account;
        }
        
        // Return 0 if the passwords were invalid
        return 0;
        
        Account:
        {
            // Get our classname
            $class = "Wowlib\\{$this->emulator}\\Account";
            if(!class_exists($class, false)) $class = "Wowlib\\Account";
            return new $class($result, $this);
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: accountExists()
| ---------------------------------------------------------------
|
| This function queries the accounts table and finds if the given
|   account ID exists.
|
| @Param: (Int | String) $id - The account ID we are checking for,
|   or the account username
| @Return (Int) - TRUE if the id exists, FALSE otherwise
|
*/
    public function accountExists($id)
    {
        // Get our table and column names
        $table = $this->config['accountTable'];
        $colid = $this->config['accountColumns']['id'];
        $username = $this->config['accountColumns']['username'];
        
        // Check the Realm DB for this username / account ID
        if(is_int($id))
            $query = "SELECT `{$username}` FROM `{$table}` WHERE `{$colid}`=?";
        else
            $query = "SELECT `{$colid}` FROM `{$table}` WHERE `{$username}` LIKE ? LIMIT 1";

        // If the result is NOT false, we have a match, username is taken
        $res = $this->DB->query( $query, array($id) )->fetchColumn();
        return ($res !== false);
    }
    
/*
| ---------------------------------------------------------------
| Method: emailExists()
| ---------------------------------------------------------------
|
| This function queries the accounts table and finds if the given
|   email exists.
|
| @Param: (String) $email - The email we are checking for
| @Return (Bool) - TRUE if the id exists, FALSE otherwise
|
*/
    public function emailExists($email)
    {
        // Get our table and column names
        $table = $this->config['accountTable'];
        $colid = $this->config['accountColumns']['email'];
        $username = $this->config['accountColumns']['username'];
        
        // Check the Realm DB for this username
        $query = "SELECT `{$username}` FROM `{$table}` WHERE `{$colid}`=?";
        $res = $this->DB->query( $query, array($email) )->fetchColumn();
        
        // If the result is NOT false, we have a match, username is taken
        return ($res !== false);
    }

/*
| ---------------------------------------------------------------
| Function: accountBanned()
| ---------------------------------------------------------------
|
| Checks the realm database if the account is banned
|
| @Param: (Int) $account_id - The account id we are checking
| @Return (Bool) Returns TRUE if the account is banned
|
*/
    public function accountBanned($account_id)
    {
        // Get our table and column names
        $table = $this->config['bannedTable'];
        $bannedCond = $this->config['conditionIfBanned'];
        $id = $this->config['bannedColumns']['accountId'];
        
        // Build the query
        $query = "SELECT COUNT({$id}) FROM `{$table}` WHERE {$bannedCond} AND `{$id}`=?";
        $check = $this->DB->query( $query, array($account_id) )->fetchColumn();
        return ($check !== false && $check > 0) ? true : false;
    }

/*
| ---------------------------------------------------------------
| Function: ipBanned()
| ---------------------------------------------------------------
|
| Checks the realm database if the users IP is banned
|
| @Param: (String) $ip - The IP we are checking
| @Return (Bool) Returns TRUE if the account is banned
|
*/
    public function ipBanned($ip)
    {
        // Get our table and column names
        $table = $this->config['ipBannedTable'];
        $id = $this->config['ipBannedColumns']['ip'];
        
        // Build the Query
        $query = "SELECT COUNT({$id}) FROM `{$table}` WHERE `{$id}`=?";
        $check = $this->DB->query( $query, array($ip) )->fetchColumn();
        return ($check !== FALSE && $check > 0) ? true : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: banAccount()
| ---------------------------------------------------------------
|
| Bans a user account
|
| @Param: (Int) $id - The account ID
| @Param: (String) $banreason - The reason user is being banned
| @Param: (String) $unbandate - The unban date timestamp
| @Param: (String) $banedby - Who is banning the user?
| @Param: (Bool) $banip - Ban ip as well?
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function banAccount($id, $banreason, $unbandate = NULL, $bannedby = 'Admin', $banip = false)
    {
        // Check for account existance
        if(!$this->accountExists($id)) return false;
        
        // Get our table and column names
        $cols = $this->config['bannedColumns'];
        $table = $this->config['bannedTable'];

        // Make sure our unbandate is set, 1 year default
        ($unbandate == NULL) ? $unbandate = (time() + 31556926) : '';
        $data = array("{$cols['accountId']}" => $id);
        
        // Add supported columns
        if($cols['banTime']) $data[ $cols['banTime'] ] = time();
        if($cols['unbanTime']) $data[ $cols['unbanTime'] ] = $unbandate;
        if($cols['bannedBy']) $data[ $cols['bannedBy'] ] = $bannedby;
        if($cols['banReason']) $data[ $cols['banReason'] ] = $banreason;
        if($cols['active']) $data[ $cols['active'] ] = 1;
        
        // Insert
        $result = $this->DB->insert($table, $data);
        
        // Do we ban the IP as well?
        return ($banip && $result) ? $this->banAccountIp($id, $banreason, $unbandate, $bannedby) : $result;
    }
    
/*
| ---------------------------------------------------------------
| Method: banAccountIp()
| ---------------------------------------------------------------
|
| Bans an accounts IP address
|
| @Param: (Int) $id - The account ID
| @Param: (String) $banreason - The reason user is being banned
| @Param: (String) $unbandate - The unban date timestamp
| @Param: (String) $banedby - Who is banning the user?
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function banAccountIp($id, $banreason, $unbandate = NULL, $bannedby = 'Admin')
    {
        // Get our table and column names
        $cid = $this->config['accountColumns']['id'];
        $lip = $this->config['accountColumns']['lastIp'];
        $table = $this->config['accountTable'];
        if($lip == false) return false;
        
        // Check for account existance
        $query = "SELECT `{$lip}` FROM `{$table}` WHERE `{$cid}`=?";
        $ip = $this->DB->query( $query, array($id) )->fetchColumn();
        if(!$ip) return false;
        
        // Check if the IP is already banned or not
        if( $this->ipBanned($ip) ) return true;
        
        // Get our table and column names
        $cols = $this->config['bannedColumns'];
        $table = $this->config['ipBannedTable'];

        // Make sure our unbandate is set, 1 year default
        ($unbandate == NULL) ? $unbandate = (time() + 31556926) : '';
        $data = array("{$cols['ip']}" => $ip); 
        
        // Add supported columns
        if($cols['banTime']) $data[ $cols['banTime'] ] = time();
        if($cols['unbanTime']) $data[ $cols['unbanTime'] ] = $unbandate;
        if($cols['bannedBy']) $data[ $cols['bannedBy'] ] = $bannedby;
        if($cols['banReason']) $data[ $cols['banReason'] ] = $banreason;
        
        // Return the insert result
        return $this->DB->insert($table, $data);
    }
    
/*
| ---------------------------------------------------------------
| Method: unbanAccount()
| ---------------------------------------------------------------
|
| Un-Bans a user account
|
| @Param: (Int) $id - The account ID
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function unbanAccount($id)
    {
        // Check if the account is not Banned
        if( !$this->accountBanned($id) ) return true;
        
        // Check for account existance
        return $this->DB->update("account_banned", array('active' => 0), "`id`=".$id);
    }
    
/*
| ---------------------------------------------------------------
| Method: unbanAccountIp()
| ---------------------------------------------------------------
|
| Un-Bans a users account IP
|
| @Param: (Int) $id - The account ID
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function unbanAccountIp($id)
    {
        // Get our table and column names
        $cid = $this->config['accountColumns']['id'];
        $lip = $this->config['accountColumns']['lastIp'];
        $table = $this->config['accountTable'];
        if($lip == false) return false;
        
        // Check for account existance
        $query = "SELECT `{$lip}` FROM `{$table}` WHERE `{$cid}`=?";
        $ip = $this->DB->query( $query, array($id) )->fetchColumn();
        if(!$ip) return false;
        
        // Check if the IP is banned or not
        if( !$this->ipBanned($ip) ) return true;
        
        // Get our table and column names
        $table = $this->config['ipBannedTable'];
        $col =  $this->config['ipBannedColumns']['ip'];
        
        // Check for account existance
        return $this->DB->delete($table, "`{$col}`=".$ip);
    }
    
/*
| ---------------------------------------------------------------
| Method: deleteAccount()
| ---------------------------------------------------------------
|
| Un-Bans a users account IP
|
| @Param: (Int) $id - The account ID
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function deleteAccount($id)
    {
        // Delete any bans
        $this->unbanAccount($id);
        
        // Get our table and column names
        $table = $this->config['accountTable'];
        $col = $this->config['accountColumns']['id'];
        
        // Delete the account
        return $this->DB->delete($table, "`{$col}`=".$id);
    }
    
/*
| ---------------------------------------------------------------
| Function: expansions()
| ---------------------------------------------------------------
|
| Returns the expansion level as defined below:
|
| @Return (Int)
|   0 => None, Base Game
|   1 => Burning Crusade
|   2 => WotLK
|   3 => Cata
|   4 => MoP
|
*/
    
    public function expansionLevel()
    {
        return (int) $this->config['expansionLevel'];
    }
    
/*
| ---------------------------------------------------------------
| Function: expansionToBit()
| ---------------------------------------------------------------
|
| Returns the Database ID of the given expansion
|
| @Return (Int)
|
*/
    
    public function expansionToBit($e)
    {
        if(!isset($this->config['expansionToBit'][$e])) return false;
        return (int) $this->config['expansionToBit'][$e];
    }
    
/*
| ---------------------------------------------------------------
| Function: numAccounts()
| ---------------------------------------------------------------
|
| This methods returns the number of accounts in the accounts table.
|
| @Return (Int) The number of accounts
|
*/
    
    public function numAccounts()
    {
        // Get our table and column names
        $table = $this->config['accountTable'];
        $col = $this->config['accountColumns']['id'];
        return $this->DB->query("SELECT COUNT(`{$col}`) FROM `{$table}`")->fetchColumn();
    }
    
/*
| ---------------------------------------------------------------
| Function: numBannedAccounts()
| ---------------------------------------------------------------
|
| This methods returns the number of accounts in the accounts table.
|
| @Return (Int) The number of accounts
|
*/
    
    public function numBannedAccounts()
    {
        // Get our table and column names
        $table = $this->config['bannedTable'];
        $col = $this->config['bannedColumns']['accountId'];
        return $this->DB->query("SELECT COUNT(`{$col}`) FROM `{$table}` WHERE `active` = 1")->fetchColumn();
    }
    
/*
| ---------------------------------------------------------------
| Function: numInactiveAccounts()
| ---------------------------------------------------------------
|
| This methods returns the number of accounts that havent logged
|   in withing the last 3 months
|
| @Return (Int) The number of accounts
|
*/
    
    public function numInactiveAccounts()
    {
        // Get our table and column names
        $table = $this->config['accountTable'];
        $id = $this->config['accountColumns']['id'];
        $ll = $this->config['accountColumns']['lastLogin'];
        
        // 90 days or older
        $time = time() - 7776000;
        $query = "SELECT COUNT(`{$id}`) FROM `{$table}` WHERE UNIX_TIMESTAMP(`{$ll}`) <  $time";
        return $this->DB->query( $query )->fetchColumn();
    }
    
/*
| ---------------------------------------------------------------
| Function: numActiveAccounts()
| ---------------------------------------------------------------
|
| This methods returns the number of accounts that have logged
|   in withing the last 24 hours
|
| @Return (Int) The number of accounts
|
*/
    
    public function numActiveAccounts()
    {
        // Get our table and column names
        $table = $this->config['accountTable'];
        $id = $this->config['accountColumns']['id'];
        $ll = $this->config['accountColumns']['lastLogin'];
        
        // 24 hours or sooner
        $time = date("Y-m-d H:i:s", time() - 86400);
        $query = "SELECT COUNT(`{$id}`) FROM `{$table}` WHERE `{$ll}` BETWEEN  '$time' AND NOW()";
        return $this->DB->query( $query )->fetchColumn();
    }
    
/*
| -------------------------------------------------------------------------------------------------
|                                           Helper Methods
| -------------------------------------------------------------------------------------------------
*/
    
/*
| ---------------------------------------------------------------
| Method: getConfig()
| ---------------------------------------------------------------
|
| This method returns the config array
|
| @Return: (Array)
|
*/
    public function getConfig()
    {
        return $this->config;
    }
    
/*
| ---------------------------------------------------------------
| Method: getColumnById()
| ---------------------------------------------------------------
|
| This method returns the column name for the given ID's
|
| @Param: (String) $table - The table ID
| @Param: (String) $col - The column ID ID
| @Return: (String)
|
*/
    public function getColumnById($table, $col)
    {
        // Make sure the config key exists
        if(!isset($this->config["{$table}Columns"][$col])) return false;
        
        return $this->config["{$table}Columns"][$col];
    }
    
/*
| ---------------------------------------------------------------
| Method: getDB()
| ---------------------------------------------------------------
|
| This method returns the database connection object to the realm
| database
|
| @Return: (Object)
|
*/
    public function getDB()
    {
        return $this->DB;
    }
    
/*
| -------------------------------------------------------------------------------------------------
|                               Realm & Account Table Query Builder
| -------------------------------------------------------------------------------------------------
*/
    protected function _buildQuery($mode, $config)
    {
        // Grab our columns and table names
        if($mode == 'R')
        {
            $cols = $this->config['realmColumns'];
            $table = $this->config['realmTable'];
        }
        else
        {
            $cols = $this->config['accountColumns'];
            $table = $this->config['accountTable'];
        }
        
        // Filter out false column names
        $columns = array();
        foreach($cols as $c)
        {
            if($c !== false) $columns[] = $c;
        }
        
        // Prepare the column names
        $cols = "`". implode('`, `', $columns) ."`";
    
        // pre build the query...
        $query = "SELECT {$cols} FROM `{$table}`";
        
        // Append Where statement
        if($config['where'] != null) $query .= " WHERE {$config['where']}";
        
        // Append OrderBy statement
        $dir = (strtoupper($config['direction']) == 'ASC') ? 'ASC' : 'DESC';
        if($config['orderby'] != null) $query .= " ORDER BY `{$config['orderby']}` {$dir}";
        
        // Append Limits
        $query .= " LIMIT {$config['offset']}, {$config['limit']}";
        
        // Return the query
        return $query;
    } 
    
    protected function prepare($mode, $config)
    {
        // Append Limits
        $query = $this->_buildQuery($mode, $config);
        
        // Prepare the statement, and bind params
        $stmt = $this->DB->prepare($query);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        if(is_array($config['bind']) && !empty($config['bind']))
        {
            foreach($config['bind'] as $key => $var)
            {
                if(is_int($var))
                    $stmt->bindParam($key, $var, \PDO::PARAM_INT);
                else
                    $stmt->bindParam($key, $var, \PDO::PARAM_STR, strlen($var));
            }
        }
        
        // Return the statement
        return $stmt;
    }
}
// EOF