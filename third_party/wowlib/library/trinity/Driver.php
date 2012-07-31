<?php
/*
| ---------------------------------------------------------------
| Config > accountTable
| ---------------------------------------------------------------
|
| This is the table name where the accounts are stored
*/
    $config['accountTable'] = 'account';

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
        'id' => 'id',
        'username' => 'username',
        'password' => false,
        'shaPassword' => 'sha_pass_hash',
        'sessionkey' => 'sessionkey',
        'v' => 'v',
        's' => 's',
        'email' => 'email',
        'joindate' => 'joindate',
        'lastIp' => 'last_ip',
        'locked' => 'locked',
        'lastLogin' => 'last_login',
        'expansion' => 'expansion'
    );

/*
| ---------------------------------------------------------------
| Config > realmTable
| ---------------------------------------------------------------
|
| This is the table name where the realmlist is stored. Set to
| false if this emulator does not store realms in the database
*/
    $config['realmTable'] = 'realmlist';
    
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
    $config['realmColumns'] = array(
        'id' => 'id',
        'name' => 'name',
        'address' => 'address',
        'port' => 'port',
        'type' => 'icon',
        'population' => 'population',
        'gamebuild' => 'gamebuild'
    );
    
/*
| ---------------------------------------------------------------
| Config > uptimeTable
| ---------------------------------------------------------------
|
| This is the table name where the uptime is stored. Set to
| false if this emulator does not store realms in the database
*/
    $config['uptimeTable'] = 'uptime';
    
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
    $config['uptimeColumns'] = array(
        'realmId' => 'realmid',
        'startTime' => 'starttime'
    );

/*
| ---------------------------------------------------------------
| Config > bannedTable
| ---------------------------------------------------------------
|
| This is the table name where the banned accounts are stored. Set to
| false if this emulator does not store banned accounts in the 
| database
*/
    $config['bannedTable'] = 'account_banned';
    
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
        'accountId' => 'id',
        'bannedBy' => 'bannedby',
        'banReason' => 'banreason',
        'banTime' => 'bandate',
        'unbanTime' => 'unbandate',
        'active' => 'active'
    );
    
    $config['conditionIfBanned'] = '`active`=1';

/*
| ---------------------------------------------------------------
| Config > ipBannedTable
| ---------------------------------------------------------------
|
| This is the table name where the banned IP's are stored. Set to
| false if this emulator does not store bannedIP's in the database
*/
    $config['ipBannedTable'] = 'ip_banned';
    
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
        'bannedBy' => 'bannedby',
        'banReason' => 'banreason',
        'banTime' => 'bandate',
        'unbanTime' => 'unbandate',
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
        1 => 1,
        2 => 2
    );