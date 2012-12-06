<?php
/*
| ---------------------------------------------------------------
| $Config Array
| ---------------------------------------------------------------
|
| The Config array is used to provide the basic information, that
| the wowlib needs to operation in this core / revision. At each
| section, there will be an explanation of what each array key is
| for, and how to properly fill it in. Donot change the variables 
| in this first section (the array containing 'configVersion' ).
|
*/
    $config = array(
        'configVersion' => '1.0'
    );


/*
| ---------------------------------------------------------------
| Config > race
| ---------------------------------------------------------------
|
| This is a two dimensional array of raceId => Race Text for each
| faction. The raceID needs to match that of the database race ID
*/
    $config['race'] = array(
        'alliance' => array(
            1 => 'Human',
            3 => 'Dwarf',
            4 => 'Night Elf',
            7 => 'Gnome',
            11 => 'Dranei'
        ), 
        'horde' => array(
            2 => 'Orc',
            5 => 'Undead',
            6 => 'Tauren',
            8 => 'Troll',
            10 => 'Bloodelf',
        )
    );

/*
| ---------------------------------------------------------------
| Config > class
| ---------------------------------------------------------------
|
| This config var is an array of classId => class Text for each
| class. The classID needs to match that of the database class ID
*/
    $config['class'] = array(
        1 => 'Warrior',
        2 => 'Paladin',
        3 => 'Hunter',
        4 => 'Rogue',
        5 => 'Priest',
        6 => 'Death Knight',
        7 => 'Shaman',
        8 => 'Mage',
        9 => 'Warlock',
        11 => 'Druid'
    );

/*
| ---------------------------------------------------------------
| Config > gender
| ---------------------------------------------------------------
|
| This config var is an array of genderId => Gender Text for each
| gender. The genderID needs to match that of the database gender ID
*/
    $config['gender'] = array(
        0 => 'Male',
        1 => 'Female',
        2 => 'Unknown'
    );

/*
| ---------------------------------------------------------------
| Config > characterTable
| ---------------------------------------------------------------
|
| This is the table name where the characters are stored
*/
    $config['characterTable'] = 'characters';

/*
| ---------------------------------------------------------------
| Config > characterColumns
| ---------------------------------------------------------------
|
| This config var is an  array of 'column ID' => 'column Name' of
| the characters table. Donot change the array keys! as they are 
| used to fetch the individual column names. set value to false if 
| a column name cant be supplied
*/
    $config['characterColumns'] = array(
        'account' => 'account', 
        'guid' => 'guid', 
        'name' => 'name', 
        'race' => 'race', 
        'class' => 'class', 
        'gender' => 'gender', 
        'level' => 'level', 
        'xp' => 'xp', 
        'money' => 'money', 
        'posX' => 'position_x', 
        'posY' => 'position_y', 
        'posZ' => 'position_z', 
        'mapId' => 'map', 
        'orientation' => 'orientation', 
        'online' => 'online',
        'timeplayed' => 'totaltime', 
        'loginFlags' => 'at_login', 
        'zoneId' => 'zone', 
        'totalKills' => 'totalKills',
        'arenaPoints' => 'arenaPoints', 
        'totalHonorPoints' => 'totalHonorPoints'
    );

/*
| ---------------------------------------------------------------
| Config > deleteCharacterTables
| ---------------------------------------------------------------
|
| This config var is an array of 'table name' => 'character Guid Column Name'
| This method is used when a character is getting deleted. It takes
| the array key as a table name, and the value as the character ID
| column.
*/
    $config['deleteCharacterTables'] = array(
        'characters' => 'guid'
    );

/*
| ---------------------------------------------------------------
| Config > loginFlags
| ---------------------------------------------------------------
|
| This config var is used for trinity / mangos based servers. Each
| array key represents the flag name, and the value is the bit value
| for that flag. All values not supported by this core revision
| should be set to false. Donot change the array keys! as they are
| used by the characters master class to get the bit values.
*/
    $config['loginFlags'] = array(
        'rename' => 1,
        'customize' => 8,
        'change_race' => false,
        'change_faction' => false,
        'reset_spells' => 2,
        'reset_talents' => 4,
        'reset_pet_talents' => 16
    );
?>