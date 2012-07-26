<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author:       Wilson212
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/

// All namespace paths must be Uppercase first letter! Format: "Wowlib\<Emulator>\<Wowlib_name>"
namespace Wowlib\Mangos\_default;

class Characters implements \Wowlib\iCharacters
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
    public function __construct($parent)
    {
        // If the characters database is offline, throw an exception!
        if(!is_object($parent->CDB)) throw new \Exception('Character database offline');
        
        // Set our database conntection
        $this->DB = $parent->CDB;
        
        // Include our character extension file
        require path( dirname(__FILE__), 'extensions', 'Character.php');
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
        $exists = $this->DB->query( $query, array($name) )->fetchColumn();
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
            $query = "SELECT COUNT(`online`) FROM `characters` WHERE `online`='1' AND (`race` = 1 OR `race` = 3 OR `race` = 4 OR `race` = 7 OR `race` = 11)";
        elseif($faction == 2): // Horde
            $query = "SELECT COUNT(`online`) FROM `characters` WHERE `online`='1' AND (`race` = 2 OR `race` = 5 OR `race` = 6 OR `race` = 8 OR `race` = 10)";
        else: // Both
            $query = "SELECT COUNT(`online`) FROM `characters` WHERE `online`='1'";
        endif;
        
        // Return the query result
        return $this->DB->query( $query )->fetchColumn();
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
        return $this->DB->query( $query )->fetchAll();
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
        $list = $this->DB->query( $query )->fetchAll();
        
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
        return $this->DB->query( $query )->fetchAll();
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
// EOF