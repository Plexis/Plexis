<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author:       Wilson212
| Copyright:    Copyright (c) 2012, Steven Wilson, Tony Hudgins
| License:      GNU GPL v3
|
*/

// All namespace paths must be Uppercase first letter! Format: "Wowlib\<wowlib_name>"
namespace Wowlib\_default;

class Characters
{
    // Our DB Connection
    public $DB;
    
    // Array of classes, races, and genders
    public $info = array(
        'race' => array(
            1 => 'Human',
            2 => 'Orc',
            3 => 'Dwarf',
            4 => 'Night Elf',
            5 => 'Undead',
            6 => 'Tauren',
            7 => 'Gnome',
            8 => 'Troll',
            9 => 'Goblin',
            10 => 'Bloodelf',
            11 => 'Dranei'
        ),
        'class' => array(
            1 => 'Warrior',
            2 => 'Paladin',
            3 => 'Hunter',
            4 => 'Rogue',
            5 => 'Priest',
            6 => 'Death_Knight',
            7 => 'Shaman',
            8 => 'Mage',
            9 => 'Warlock',
            11 => 'Druid'
        ),
        'gender' => array(
            0 => 'Male',
            1 => 'Female',
            2 => 'None'
        ),
        'faction' => array(
            0 => 'Horde',
            1 => 'Alliance'
        )
    );
    
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($connection)
    {
        // Set oru database conntection, which is passed when this class is Init.
        $this->DB = $connection;
    }
    
/*
| ---------------------------------------------------------------
| Method: nameExists
| ---------------------------------------------------------------
|
| This method is used to determine if a character name is available
|
| @Param: (String) $name - The character name we are looking up
| @Retrun:(Bool): TRUE if the name is available, FALSE otherwise
|
*/     
    public function nameExists($name)
    {
        // Build our query
        $query = "SELECT `guid` FROM `characters` WHERE `name`=?";
        $exists = $this->DB->query( $query, array($name) )->fetch_column();
        return ($exists !== false) ? true : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: fetch
| ---------------------------------------------------------------
|
| This method is used to return an array of character information
|
| @Param: (Int) $id - The character ID
| @Retrun: (Object) Returns a Character Object class
|
*/  
    public function fetch($id)
    {
        // Build our query
        try {
            $character = new Character($id, $this);
        }
        catch (\Exception $e) {
            $character = false;
        }
        
        return $character;
    }

/*
| ---------------------------------------------------------------
| Method: getOnlineCount
| ---------------------------------------------------------------
|
| Returns the amount of characters currently online
|
| @Param: (Int) $faction - Faction ID, 1 = Ally, 2 = Horde, 0 = Both
| @Retrun: (Int): Returns the amount on success, FALSE otherwise
|
*/     
    public function getOnlineCount($faction = 0)
    {
        
        if($faction == 1): // Alliance
            $query = "SELECT COUNT(*) FROM `characters` WHERE `online`='1' AND (`race` = 1 OR `race` = 3 OR `race` = 4 OR `race` = 7 OR `race` = 11)";
        elseif($faction == 2): // Horde
            $query = "SELECT COUNT(*) FROM `characters` WHERE `online`='1' AND (`race` = 2 OR `race` = 5 OR `race` = 6 OR `race` = 8 OR `race` = 10)";
        else: // Both
            $query = "SELECT COUNT(*) FROM `characters` WHERE `online`='1'";
        endif;
        
        // Return the query result
        return $this->DB->query( $query )->fetch_column();
    }
    
/*
| ---------------------------------------------------------------
| Method: getOnlineList
| ---------------------------------------------------------------
|
| This method returns a list of characters online
|
| @Param: (Int) $limit - The number of results we are recieveing
| @Param: (Int) $start - The result we start from (example: $start = 50
|   would return results 50-100)
| @Param: (Int) $faction - Faction ID, 1 = Ally, 2 = Horde, 0 = Both
| @Retrun: (Array): An array of characters
|
*/     
    public function getOnlineList($limit = 100, $start = 0, $faction = 0)
    {
        switch($faction)
        {
            case 1:
                // Alliance Only
                $query = "SELECT `guid`, `name`, `race`, `class`, `gender`, `level`, `zone`  FROM `characters` WHERE `online`='1' AND 
                    (`race` = 1 OR `race` = 3 OR `race` = 4 OR `race` = 7 OR `race` = 11) LIMIT $start, $limit";
                break;
            case 2:
                // Horde Only
                $query = "SELECT `guid`, `name`, `race`, `class`, `gender`, `level`, `zone`  FROM `characters` WHERE `online`='1' AND 
                    (`race` = 2 OR `race` = 5 OR `race` = 6 OR `race` = 8 OR `race` = 10) LIMIT $start, $limit";
                break;
            default :
                // Both factions
                $query = "SELECT `guid`, `name`, `race`, `class`, `gender`, `level`, `zone`  FROM `characters` WHERE `online`='1' LIMIT $start, $limit";
                break;
        }
        
        // Return the query result
        return $this->DB->query( $query )->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Method: listCharacters
| ---------------------------------------------------------------
|
| This method is used to list all the characters from the characters
| database.
|
| @Param: (Int) $acct - The account ID. 0 = all characters from all
|   accounts
| @Param: (Int) $limit - The number of results we are recieveing
| @Param: (Int) $start - The result we start from (example: $start = 50
|   would return results 50-100)
| @Retrun: (Array): An array of characters
|
*/
    public function listCharacters($acct = 0, $limit = 50, $start = 0)
    {        
        // Build our query
        if($acct == 0):
            $query = "SELECT `guid`, `name`, `race`, `gender`, `class`, `level`, `zone` FROM `characters` LIMIT {$start}, {$limit}";
        else:
            $query = "SELECT `guid`, `name`, `race`, `gender`, `class`, `level`, `zone` FROM `characters` WHERE `account`= {$acct} LIMIT {$start}, {$limit}";
        endif;
        
        // Query the database
        $list = $this->DB->query( $query )->fetch_array();
        
        // If we have a false return, then there was nothing to select
        return ($list === FALSE) ? array() : $list;
    }
    
/*
| ---------------------------------------------------------------
| Method: listCharactersDatatables
| ---------------------------------------------------------------
|
| This method returns a list of characters, formatted for datatables
| ajax.
|
| @Param: (Int) $acct - The account ID. 0 = all characters from all
|   accounts
| @Param: (Bool) $online - Only list online players?
| @Retrun: (Array): An array of characters
|
*/     
    public function listCharactersDatatables($acct = 0, $online = false)
    {
        // Load the ajax model
        $ajax = load_class('Loader')->model("Ajax_Model", "ajax");
  
        /* 
        * Dwsc: Array of database columns which should be read and sent back to DataTables. 
        * Format: id, name, character level, race ID, class ID, Gender ID, Zone ID, Account ID, And status
        */
        $cols = array( 'guid', 'name', 'level', 'race', 'class', 'gender', 'zone', 'account', 'online' );
        
        /* Character ID column name */
        $index = "guid";
        
        /* characters table name to use */
        $table = "characters";
        
        /* where statment */
        $where = ($online == true) ? '`online` = 1' : '';
        
        /* And Where statment */
        if($acct != 0) $where .= ($online == true) ? ' AND `account` = '. $acct : '`account` = '. $acct;
        
        /* Process the request */
        return $ajax->process_datatables($cols, $index, $table, $where, $this->DB);
    }

/*
| ---------------------------------------------------------------
| Method: topKills
| ---------------------------------------------------------------
|
| This method returns a list of the top chacters with kills
|
| @Param: (Int) $faction - Faction ID, 1 = Ally, 2 = Horde, 0 = Both
| @Param: (Int) $limit - The number of results we are recieveing
| @Param: (Int) $start - The result we start from (example: $start = 50
|   would return results 50-100)
| @Retrun: (Array): An array of characters ORDERED by kills
|
*/      
    function topKills($faction, $limit, $start)
	{
		// Alliance
		if($faction == 1)
		{			
			$row = "SELECT `guid`, `name`, `race`, `class`, `gender`, `level` FROM `characters` WHERE `totalkills` > 0 AND (
				`race` = 1 OR `race` = 3 OR `race` = 4 OR `race` = 7 OR `race` = 11) ORDER BY `totalkills` DESC LIMIT $start, $limit";
		}
		else # Horde
		{			
			$row = "SELECT `guid`, `name`, `race`, `class`, `gender`, `level` FROM `characters` WHERE `totalkills` > 0 AND (
				`race` = 2 OR `race` = 5 OR `race` = 6 OR `race` = 8 OR `race` = 10) ORDER BY `totalkills` DESC LIMIT $start, $limit";
		}
		
        // Return the query result
        return $this->DB->query( $query )->fetch_array();
	}
 
/*
| ---------------------------------------------------------------
| Method: delete
| ---------------------------------------------------------------
|
| This method removes the character from the characters DB
|
| @Param: (Int) $id - The character id we are deleteing
| @Retrun: (Bool): True on success, FALSE otherwise
|
*/ 
    public function delete($id)
    {
        // A list of (table => character_id_col_name) to remove character info from
        // The more tables listed, the more we can delete this character
        $tables = array(
            'characters' => 'guid'
        );
        
        foreach($tables as $table => $col)
        {
            $result = $this->DB->delete($table, "`$col`=$id");
            if($result === false) return false;
        }
        
        return true;
    }


/*
| -------------------------------------------------------------------------------------------------
|                               AT LOGIN FLAGS
| -------------------------------------------------------------------------------------------------
*/


/*
| ---------------------------------------------------------------
| Method: loginFlags()
| ---------------------------------------------------------------
|
| This method is used to return a list of "at login" flags this
| core / revision is able to do. Please note, the functions must
| exist!
|
| @Retrun: (Array): An array of true / false flags
|
*/ 
    public function loginFlags()
    {
        return array(
            'rename' => true,
            'customize' => true,
            'change_race' => false,
            'change_faction' => false,
            'reset_spells' => false,
            'reset_talents' => true,
            'reset_pet_talents' => false
        );
    }
    
/*
| ---------------------------------------------------------------
| Method: flagToBit()
| ---------------------------------------------------------------
|
| This method is used to return the bitmask flag for the givin flag 
| name
|
| @Param: (String) $flag - The flag name we are getting the bit for
| @Retrun: (Int | Bool): The bitmask on success, False otherwise
|
*/
    public function flagToBit($flag)
    {
        // only list available flags
        $flags = array(
            'rename' => 1,
            'reset_spells' => 2,
            'reset_talents' => 4,
            'customize' => 8,
            'reset_pet_talents' => 16
        );
        
        return (isset($flags[ $flag ])) ? $flags[ $flag ] : false;
    }
    
    
/*
| -------------------------------------------------------------------------------------------------
|                               HELPER FUNCTIONS
| -------------------------------------------------------------------------------------------------
*/


    public function raceToText($id)
    {
        // Check if the race is set, if not then Unknown
        if(isset($this->info['race'][$id]))
        {
            return $this->info['race'][$id];
        }
        return "Unknown";
    }

    public function classToText($id)
    {
        // Check if the class is set, if not then Unknown
        if(isset($this->info['class'][$id]))
        {
            return $this->info['class'][$id];
        }
        return "Unknown";
    }

    public function genderToText($id)
    {
        // Check if the gender is set, if not then Unknown
        if(isset($this->info['gender'][$id]))
        {
            return $this->info['gender'][$id];
        }
        return "Unknown";
    }
}




/* 
| -------------------------------------------------------------- 
| Character Object Class
| --------------------------------------------------------------
|
| Author:       Wilson212
| Copyright:    Copyright (c) 2012, Steven Wilson, Tony Hudgins
| License:      GNU GPL v3
|
*/
class Character
{
    // Our DB Connection and Characters parent class
    protected $DB;
    protected $parent;
    
    // Our character variables
    protected $guid;
    protected $data = array();
    
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($guid, $parent)
    {
        // Set oru database conntection, which is passed when this class is Init.
        $this->DB = $parent->DB;
        $this->parent = $parent;
        $this->guid = $guid;
        
        // Load the character
        $query = "SELECT 
            `account`, 
            `name`, 
            `race`, 
            `class`, 
            `gender`, 
            `level`, 
            `xp`, 
            `money`, 
            `position_x`, 
            `position_y`, 
            `position_z`, 
            `map`, 
            `orientation`,
            `online`,
            `totaltime`,
            `at_login`,
            `zone`,
            `arenaPoints`,
            `totalHonorPoints`,
            `totalKills`
            FROM `characters` WHERE `guid`= $guid;";
        $this->data = $this->DB->query($query)->fetch_row();
        
        // Make sure we didnt get a false return
        if(!is_array($this->data)) throw new \Exception('Character doesnt exist');
    }
    
/*
| ---------------------------------------------------------------
| Method: save()
| ---------------------------------------------------------------
|
| This method saves the current characters data in the database
|
| @Retrun: (Bool): An array of true / false flags
|
*/ 
    public function save()
    {
        // Update all the characters data in the DB
        return $this->DB->update('characters', $this->data, "`guid`= $this->guid");
    }
    
/*
| ---------------------------------------------------------------
| Method: isOnline
| ---------------------------------------------------------------
|
| This method returns a bool based on if a character is online.
|
| @Retrun: (Bool) TRUE if the cahracter is online, FALSE otherwise
|
*/  
    public function isOnline()
    {
        return (bool) $this->data['online'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getAccountId
| ---------------------------------------------------------------
|
| This method returns the account ID that belongs to this character
|
| @Retrun: (Int)
|
*/  
    public function getAccountId()
    {
        return (int) $this->data['account'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getName
| ---------------------------------------------------------------
|
| This method returns the characters name
|
| @Retrun: (String)
|
*/  
    public function getName()
    {
        return (string) $this->data['name'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getLevel
| ---------------------------------------------------------------
|
| This method returns the characters level
|
| @Retrun: (Int)
|
*/  
    public function getLevel()
    {
        return (int) $this->data['level'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getClass
| ---------------------------------------------------------------
|
| This method returns the characters class
|
| @Param: (Bool) $asText - Return the class text name?
| @Retrun: (String | Int)
|
*/  
    public function getClass($asText = false)
    {
        return ($asText == true) ? $this->parent->classToText($this->data['class']) : (int) $this->data['class'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getRace
| ---------------------------------------------------------------
|
| This method returns the characters race
|
| @Param: (Bool) $asText - Return the race text name?
| @Retrun: (String | Int)
|
*/  
    public function getRace($asText = false)
    {
        return ($asText == true) ? $this->parent->raceToText($this->data['race']) : (int) $this->data['race'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getGender
| ---------------------------------------------------------------
|
| This method returns the characters gender
|
| @Param: (Bool) $asText - Return the gender text name?
| @Retrun: (String | Int)
|
*/  
    public function getGender($asText = false)
    {
        return ($asText == true) ? $this->parent->genderToText($this->data['gender']) : (int) $this->data['gender'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getFaction
| ---------------------------------------------------------------
|
| Gets the faction for character id.
|
| @Retrun: (Int): Returns 1 = Ally, 0 = horde on success, 
|   FALSE otherwise (use the "===" to tell 0 from false)
|
*/ 
    public function getFaction()
    {
        // Frist we make an array of alliance race's
        $ally = array("1", "3", "4", "7", "11");

        // Now we check to see if the characters race is in the array we made before
        return (in_array($this->getRace(), $ally)) ? 1 : 0;
    }
    
/*
| ---------------------------------------------------------------
| Method: getXp
| ---------------------------------------------------------------
|
| This method returns the characters current xp
|
| @Retrun: (Int)
|
*/  
    public function getXp()
    {
        return (int) $this->data['xp'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getMoney
| ---------------------------------------------------------------
|
| This method returns the characters current money
|
| @Retrun: (Int)
|
*/  
    public function getMoney()
    {
        return (int) $this->data['money'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getPosition
| ---------------------------------------------------------------
|
| This method returns the characters position and map / zone ID
| in an array
|
| @Retrun: (Array)
|
*/  
    public function getPosition()
    {
        return array(
            'x' => $this->data['position_x'],
            'y' => $this->data['position_y'],
            'z' => $this->data['position_z'],
            'orientation' => $this->data['orientation'],
            'map' => $this->data['map'],
            'zone' => $this->data['zone']
        );
    }
    
/*
| ---------------------------------------------------------------
| Method: getTimePlayed
| ---------------------------------------------------------------
|
| This method returns the characters total time played
|
| @Retrun: (Int)
|
*/  
    public function getTimePlayed()
    {
        return (int) $this->data['timeplayed'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getTotalKills
| ---------------------------------------------------------------
|
| This method returns the characters total pvp kills
|
| @Retrun: (Int)
|
*/  
    public function getTotalKills()
    {
        return (int) $this->data['totalKills'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getTotalKills
| ---------------------------------------------------------------
|
| This method returns the characters total honor points
|
| @Retrun: (Int)
|
*/  
    public function getHonorPoints()
    {
        return (int) $this->data['totalHonorPoints'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getTotalKills
| ---------------------------------------------------------------
|
| This method returns the characters total arena points
|
| @Retrun: (Int)
|
*/  
    public function getArenaPoints()
    {
        return (int) $this->data['arenaPoints'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getLoginFlags()
| ---------------------------------------------------------------
|
| This method is used to return all login flags the character has
|
| @Retrun: (Array): An array of true / false flags
|
*/ 
    public function getLoginFlags()
    {
        // Build the dummy array
        $flags = array();
        
        // Loop through each supported flag, and assign a false value
        $supported = $this->parent->loginFlags();
        foreach($supported as $key => $flag)
        {
            $flags[$key] = false;
        }

        // Is there any flags set?
        $cflags = (int)$this->data['at_login'];
        if( $cflags == 0 ) return $flags;
        
        // Determine if each flag is true or false
        foreach($flags as $key => $flag)
        {
            $bit = $this->parent->flagToBit($key);
            $flags[$key] = ($cflags & $bit) ? true : false;
        }
        
        return $flags;
    }
    
/*
| ---------------------------------------------------------------
| Method: hasLoginFlag()
| ---------------------------------------------------------------
|
| This method is used to return a if a character has the specified
| login flag enabled
|
| @Param: (String) $name - The flag name we are getting
| @Retrun: (Bool): True if the character has the flag, False otherwise
|
*/ 
    public function hasLoginFlag($name)
    {
        // Convert flags to an int, and get our bit id
        $flags  = (int) $this->data['at_login'];
        $flagid = (int) $this->parent->flagToBit($name);
        
        // Make sure this feature is supported
        if($flagid == 0) return false;
        
        // Check, if the flag is set, return true
        return ($flags & $flagid) ? true : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: resetPosition
| ---------------------------------------------------------------
|
| This method unstuck's a character, by resetting thier position
| to their herthstone bind position
|
| @Retrun: (Bool)
|
*/ 
    public function resetPoistion()
    {
        // Now we reset the position based off of the race ID
        $query = "SELECT * FROM `character_homebind` WHERE `guid`=$this->guid";
        $pos = $this->DB->query($query)->fetch_row();
        
        // Set the position
        return $this->setPosition($pos['position_x'], $pos['position_y'], $pos['position_z'], $this->data['orientation'], $pos['map']);
    }
    
/*
| ---------------------------------------------------------------
| Method: setPosition
| ---------------------------------------------------------------
|
| This method sets a characters position based off of parameters
|
| @Param: (Float) $x - Position of the character relative to the $map's x-axis.
| @Param: (Float) $y - Position of the character relative to the $map's y-axis.
| @Param: (Float) $z - Position of the character relative to the $map's z-axis.
| @Param: (Float) $o - The direction the character is facing.
| @Param: (Int) $map - The map the character will be on.
| @Retrun: (Bool)
|
*/ 
    public function setPoistion($x, $y, $z, $o, $map)
    {
        $this->data['position_x'] = (float) $x;
        $this->data['position_y'] = (float) $y;
        $this->data['position_z'] = (float) $z;
        $this->data['orientation'] = (float) $o;
        $this->data['map'] = (int) $map;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setLoginFlag()
| ---------------------------------------------------------------
|
| This method is used to return a list of "at login" flags this
| core / revision is able to do. Please note, the functions must
| exist!
|
| @Param: (String) $name - The flag name we are settings
| @Param: (Bool) $status - True to enable flag, false to remove it
| @Retrun: (Bool): True on success, False otherwise
|
*/ 
    public function setLoginFlag($name, $status)
    {
        // Convert flags to an int, and get our bit id
        $flags  = (int) $this->data['at_login'];
        $flagid = (int) $this->parent->flagToBit($name);
        
        // Make sure this feature is supported
        if($flagid == 0) return false;
        
        // Determine if the flag is already enabled before enabling it again
        if ($status == true)
        {
            // Check, if the flag is set, return true
            if($flags != 0 && ($flags & $flagid)) return true;
            
            // Set new flag
            $newflags = $flagid + $flags;
        }
        else
        {
            // If disabling a flag, return true if its already disabled
            if($flags == 0 || ( !($flags & $flagid) )) return true;
            
            // Set new flag
            $newflags = $flags - $flagid;
        }
        
        // Update the data array
        $this->data['at_login'] = $newflags;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setAccountId
| ---------------------------------------------------------------
|
| This method sets the account ID that belongs to this character
|
| @Param: (Int) $id - The new account id
| @Retrun: (Bool)
|
*/  
    public function setAccountId($id)
    {
        if(!is_numeric($id)) return false;
        $this->data['account'] = (int) $id;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setName
| ---------------------------------------------------------------
|
| This method sets the characters name
|
| @Param: (String) $name - The new name of the character
| @Retrun: (Bool) True on success, false if name already exists
|
*/  
    public function setName($name)
    {
        // Make sure the name exists already!!
        if($this->parent->nameExists($name)) return false;
        $this->data['name'] = $name;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setLevel
| ---------------------------------------------------------------
|
| This method sets the characters level
|
| @Param: (Int) $lvl - The new level of the character
| @Retrun: (Bool)
|
*/  
    public function setLevel($lvl)
    {
        if(!is_numeric($lvl)) return false;
        $this->data['level'] = (int) $lvl;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setXp
| ---------------------------------------------------------------
|
| This sets the characters current xp
|
| @Param: (Int) $xp - The new character xp amount
| @Retrun: (Bool)
|
*/  
    public function setXp($xp)
    {
        if(!is_numeric($xp)) return false;
        $this->data['xp'] = (int) $xp;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setMoney
| ---------------------------------------------------------------
|
| This method sets the characters current money
|
| @Param: (Int) $money - The new amount of copper this character has
| @Retrun: (Bool)
|
*/  
    public function setMoney($money)
    {
        if(!is_numeric($money)) return false;
        $this->data['money'] = (int) $money;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setTotalKills
| ---------------------------------------------------------------
|
| This method sets the characters total pvp kills
|
| @Param: (Int) $kills - The new amount of total kills
| @Retrun: (Bool)
|
*/  
    public function setTotalKills($kills)
    {
        if(!is_numeric($kills)) return false;
        $this->data['totalKills'] = (int) $kills;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setTotalKills
| ---------------------------------------------------------------
|
| This method sets the characters total honor points
|
| @Param: (Int) $points - The new amount of honor points
| @Retrun: (Bool)
|
*/  
    public function setHonorPoints($points)
    {
        if(!is_numeric($points)) return false;
        $this->data['totalHonorPoints'] = (int) $points;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setTotalKills
| ---------------------------------------------------------------
|
| This method sets the characters total arena points
|
| @Param: (Int) $points - The new amount of arena points
| @Retrun: (Bool)
|
*/  
    public function setArenaPoints($points)
    {
        if(!is_numeric($points)) return false;
        $this->data['arenaPoints'] = (int) $points;
        return true;
    }
}