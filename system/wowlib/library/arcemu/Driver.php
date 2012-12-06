<?php
/*
| ---------------------------------------------------------------
| Config > accountTable
| ---------------------------------------------------------------
|
| This is the table name where the accounts are stored
*/
    $config['accountTable'] = 'accounts';

/*
| ---------------------------------------------------------------
| Config > accountColumns
| ---------------------------------------------------------------
|
| This is an array of 'columnId' => 'columnName', that the Account
| Object is to query. Donot change the array keys as they are used
| to fetch the coulmn names! If a column isnt supported, replace
| column name with false.
*/
    $config['accountColumns'] = array(
        'id' => 'acct',
        'username' => 'login',
        'password' => 'password',
        'shaPassword' => false,
        'sessionkey' => false,
        'v' => false,
        's' => false,
        'email' => 'email',
        'joindate' => false,
        'lastIp' => 'lastip',
        'locked' => false,
        'lastLogin' => 'lastlogin',
        'expansion' => 'flags'
    );

/*
| ---------------------------------------------------------------
| Config > realmTable
| ---------------------------------------------------------------
|
| This is the table name where the realmlist is stored. Set to
| false if this emulator does not store realms in the database
*/
    $config['realmTable'] = false;
    
/*
| ---------------------------------------------------------------
| Config > realmColumns
| ---------------------------------------------------------------
|
| This is an array of 'columnId' => 'columnName', that the Realm
| Object is to query. Donot change the array keys as they are used
| to fetch the coulmn names! If a column isnt supported, replace
| column name with false.
*/
    $config['realmColumns'] = array();
    
/*
| ---------------------------------------------------------------
| Config > uptimeTable
| ---------------------------------------------------------------
|
| This is the table name where the uptime is stored. Set to
| false if this emulator does not store realms in the database
*/
    $config['uptimeTable'] = false;
    
/*
| ---------------------------------------------------------------
| Config > uptimeColumns
| ---------------------------------------------------------------
|
| This is an array of 'columnId' => 'columnName', that the Realm
| Object is to query. Donot change the array keys as they are used
| to fetch the coulmn names! If a column isnt supported, replace
| column name with false.
*/
    $config['uptimeColumns'] = array();

/*
| ---------------------------------------------------------------
| Config > bannedTable
| ---------------------------------------------------------------
|
| This is the table name where the banned accounts are stored. Set to
| false if this emulator does not store banned accounts in the 
| database
*/
    $config['bannedTable'] = 'accounts';
    
/*
| ---------------------------------------------------------------
| Config > bannedColumns
| ---------------------------------------------------------------
|
| This is an array of 'columnId' => 'columnName', that the Emulator
| is to query. Donot change the array keys as they are used
| to fetch the coulmn names!
*/
    $config['bannedColumns'] = array(
        'accountId' => 'acct',
        'bannedBy' => false,
        'banReason' => 'banreason',
        'banTime' => false,
        'unbanTime' => 'banned',
        'active' => false
    );
    
    $config['conditionIfBanned'] = '`banned` > 0';

/*
| ---------------------------------------------------------------
| Config > ipBannedTable
| ---------------------------------------------------------------
|
| This is the table name where the banned IP's are stored. Set to
| false if this emulator does not store bannedIP's in the database
*/
    $config['ipBannedTable'] = 'ipbans';
    
/*
| ---------------------------------------------------------------
| Config > ipBannedColumns
| ---------------------------------------------------------------
|
| This is an array of 'columnId' => 'columnName', that the Emulator
| is to query. Donot change the array keys as they are used
| to fetch the coulmn names!
*/
    $config['ipBannedColumns'] = array(
        'ip' => 'ip',
        'bannedBy' => false,
        'banReason' => 'banreason',
        'banTime' => false,
        'unbanTime' => 'expire',
    );

/*
| ---------------------------------------------------------------
| Config > expansionLevel
| ---------------------------------------------------------------
|
| This is the expansion level of this emulator. 
|   0 => base game only
|   1 => Burning Crusade
|   2 => WotLK
|   3 => Cataclysm
|   4 => MoP
*/
    $config['expansionLevel'] = 2;
    
/*
| ---------------------------------------------------------------
| Config > expansionToBit
| ---------------------------------------------------------------
|
| This is an array of 'expansionLevel' => 'databaseValue'.
*/
    $config['expansionToBit'] = array(
        0 => 0,
        1 => 8,
        2 => 24,
        3 => 32
    );