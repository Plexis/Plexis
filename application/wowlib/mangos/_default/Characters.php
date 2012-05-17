<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author:       Tony Hudgins
| Copyright:    Copyright (c) 2012, Steven Wilson, Tony Hudgins
| License:      GNU GPL v3
|
*/

// All namespace paths must be Uppercase first letter! Format: "Wowlib\<wowlib_name>"
namespace Wowlib\_default;

class Characters
{
    // Our DB Connection
    protected $DB;
    
    // Array of classes, races, and genders
    protected $info = array(
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
        )
    );
    
/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct($connection, $wdb)
    {
        // Set oru database conntection, which is passed when this class is Init.
        $this->DB = $connection;
        
        // Load the loader class
        $this->load = load_class('Loader');
    }
    
/*
| -------------------------------------------------------------------------------------------------
|                               CHARACTER DATABASE FUNCTIONS
| -------------------------------------------------------------------------------------------------
*/

    
/*
| ---------------------------------------------------------------
| Method: character_online
| ---------------------------------------------------------------
|
| This method is used to determine if a character is online or not
|
| @Param: (Int) $id - The character id we are looking up
| @Retrun:(Bool): TRUE if the cahracter is online, FALSE otherwise
|
*/     
    public function character_online($id)
    {
        // Build our query
        $query = "SELECT `online` FROM `characters` WHERE `guid`=?";
        $online = $this->DB->query( $query, array($id) )->fetch_column();
        if($online == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, we have the characters status
        return $online;
    }
    
/*
| ---------------------------------------------------------------
| Method: character_name_exists
| ---------------------------------------------------------------
|
| This method is used to determine if a character name is available
|
| @Param: (String) $name - The character name we are looking up
| @Retrun:(Bool): TRUE if the name is available, FALSE otherwise
|
*/     
    public function character_name_exists($name)
    {
        // Build our query
        $query = "SELECT `guid` FROM `characters` WHERE `name`=?";
        $exists = $this->DB->query( $query, array($name) )->fetch_column();
        if($exists !== FALSE)
        {
            return TRUE;
        }
        
        // If we are here, the name is available
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_character_info
| ---------------------------------------------------------------
|
| This method is used to return an array of character information
|
| @Param: (Int) $id - The character ID
| @Retrun:(Array): False if the character doesnt exist, Otherwise
|   array(
|       'account' => The account ID the character belongs too
|       'id' => Character Id
|       'name' => The characters name
|       'race' => The characters race id
|       'class' => The characters class id
|       'gender' => Gender
|       'level' => Level
|       'money' => characters money
|       'xp' => Characters current level expierience
|       'online' => 1 if character online, 0 otherwise
|       'zone' => The zone ID the character is in
|   );
|
*/  
    public function get_character_info($id)
    {
        // Build our query
        $query = "SELECT `guid` as `id`, `account`, `name`, `race`, `class`, `gender`, `level`, `money`, `xp`, `online`, `zone` FROM `characters` WHERE `guid`=?";
        $account = $this->DB->query( $query, array($id) )->fetch_row();
        if($account == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the account ID. return it
        return $account;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_character_account_id
| ---------------------------------------------------------------
|
| This method is used to get the account id tied to the character
|
| @Param: (Int) $id - The character id we are looking up
| @Retrun: (Int): the account id on success, FALSE otherwise
|
*/     
    public function get_character_account_id($id)
    {
        // Build our query
        $query = "SELECT `account` FROM `characters` WHERE `guid`=?";
        $account = $this->DB->query( $query, array($id) )->fetch_column();
        if($account == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the account ID. return it
        return $account;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_character_name
| ---------------------------------------------------------------
|
| This method is used to get the characters name
|
| @Param: (Int) $id - The character id we are looking up
| @Retrun: (Int): Returns the characters name on success, FALSE otherwise
|
*/     
    public function get_character_name($id)
    {
        // Build our query
        $query = "SELECT `name` FROM `characters` WHERE `guid`=?";
        $result = $this->DB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the name. return it
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_character_level
| ---------------------------------------------------------------
|
| This method is used to get the level of a character
|
| @Param: (Int) $id - The character id we are looking up
| @Retrun: (Int): The characters level on success, FALSE otherwise
|
*/     
    public function get_character_level($id)
    {
        // Build our query
        $query = "SELECT `level` FROM `characters` WHERE `guid`=?";
        $result = $this->DB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the result. return it
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_character_race
| ---------------------------------------------------------------
|
| This method is used to get the race ID of a character
|
| @Param: (Int) $id - The character id we are looking up
| @Retrun: (Int): The characters race ID on success, FALSE otherwise
|
*/     
    public function get_character_race($id)
    {
        // Build our query
        $query = "SELECT `race` FROM `characters` WHERE `guid`=?";
        $result = $this->DB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the result. return it
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_character_class
| ---------------------------------------------------------------
|
| This method is used to get the class ID of a character
|
| @Param: (Int) $id - The character id we are looking up
| @Retrun: (Int): The characters class ID on success, FALSE otherwise
|
*/     
    public function get_character_class($id)
    {
        // Build our query
        $query = "SELECT `class` FROM `characters` WHERE `guid`=?";
        $result = $this->DB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the result. return it
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_character_gender
| ---------------------------------------------------------------
|
| This method is used to get the gender of a character
|
| @Param: (Int) $id - The character id we are looking up
| @Retrun: (Int): The characters gender (0=male, 1=female) on success, 
|   FALSE otherwise
|
*/  
    public function get_character_gender($id)
    {
        // Build our query
        $query = "SELECT `gender` FROM `characters` WHERE `guid`=?";
        $result = $this->DB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the result. return it
        return $result;
    }

/*
| ---------------------------------------------------------------
| Method: get_character_faction
| ---------------------------------------------------------------
|
| Gets the faction for character id.
|
| @Param: (Int) $id - The character id we are looking up
| @Retrun: (Int): Returns 1 = Ally, 0 = horde on success, 
|   FALSE otherwise (use the "===" to tell 0 from false)
|
*/ 
    public function get_character_faction($id)
    {
        // Frist we make an array of alliance race's
        $ally = array("1", "3", "4", "7", "11");
        
        // Get our characters current race
        $row = $this->get_character_race($id);
        if($row == FALSE)
        {
            return FALSE;
        }
        else
        {
            // Now we check to see if the characters race is in the array we made before
            if(in_array($row, $ally))
            {
                // Return that the race is alliance
                return 1;
            } 
            else 
            {
                // Race is Horde
                return 0;
            }
        }
    }

/*
| ---------------------------------------------------------------
| Method: get_character_gold
| ---------------------------------------------------------------
|
| Returns the amount of gold a character has.
|
| @Param: (Int) $id - The character id we are looking up
| @Retrun: (Int): Returns the amount on success, FALSE otherwise
|
*/     
    public function get_character_gold($id)
    {
        // First we check to make sure the character exists!
        $query = "SELECT `money` FROM `characters` WHERE `guid`=?";
        $gold = $this->DB->query( $query, array($id) )->fetch_column();
        if($gold == FALSE)
        {
            return FALSE;
        }
        else
        {
            return $gold;
        }
    }

/*
| ---------------------------------------------------------------
| Method: get_online_count
| ---------------------------------------------------------------
|
| Returns the amount of characters currently online
|
| @Param: (Int) $faction - Faction ID, 1 = Ally, 2 = Horde, 0 = Both
| @Retrun: (Int): Returns the amount on success, FALSE otherwise
|
*/     
    public function get_online_count($faction = 0)
    {
        // Alliance
        if($faction == 1)
        {
            $query = "SELECT COUNT(*) FROM `characters` WHERE `online`='1' AND (`race` = 1 OR `race` = 3 OR `race` = 4 OR `race` = 7 OR `race` = 11)";
        }

        // Horde
        elseif($faction == 2)
        {
            $query = "SELECT COUNT(*) FROM `characters` WHERE `online`='1' AND (`race` = 2 OR `race` = 5 OR `race` = 6 OR `race` = 8 OR `race` = 10)";
        }

        // Both factions
        else
        {
            $query = "SELECT COUNT(*) FROM `characters` WHERE `online`='1'";
        }
        
        // Return the query result
        return $this->DB->query( $query )->fetch_column();
    }
    
/*
| ---------------------------------------------------------------
| Method: list_characters
| ---------------------------------------------------------------
|
| This method is used to list all the characters from the characters
| database.
|
| @Param: (Int) $limit - The number of results we are recieveing
| @Param: (Int) $start - The result we start from (example: $start = 50
|   would return results 50-100)
| @Retrun: (Array): An array of characters
|
*/
    public function list_characters($limit = 50, $start = 0)
    {        
        // Build our query, and query the database
        $query = "SELECT `guid`, `name`, `race`, `gender`, `class`, `level`, `zone` FROM `characters` LIMIT ".$start.", ".$limit;
        $list = $this->DB->query( $query )->fetch_array();
        
        // If we have a false return, then there was nothing to select
        if($list === FALSE)
        {
            return array();
        }
        return $list;
    }

/*
| ---------------------------------------------------------------
| Method: get_online_list
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
    public function get_online_list($limit = 100, $start = 0, $faction = 0)
    {
        // Alliance Only
        if($faction == 1)
        {
            $query = "SELECT `guid`, `name`, `race`, `class`, `gender`, `level`, `zone`  FROM `characters` WHERE `online`='1' AND 
                (`race` = 1 OR `race` = 3 OR `race` = 4 OR `race` = 7 OR `race` = 11) LIMIT $start, $limit";
        }
        
        // Horde Only
        elseif($faction == 2)
        {
            $query = "SELECT `guid`, `name`, `race`, `class`, `gender`, `level`, `zone`  FROM `characters` WHERE `online`='1' AND 
                (`race` = 2 OR `race` = 5 OR `race` = 6 OR `race` = 8 OR `race` = 10) LIMIT $start, $limit";
        }
        
        // Both factions
        else
        {
            $query = "SELECT `guid`, `name`, `race`, `class`, `gender`, `level`, `zone`  FROM `characters` WHERE `online`='1' LIMIT $start, $limit";
        }
        
        // Return the query result
        return $this->DB->query( $query )->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Method: get_online_list_datatables
| ---------------------------------------------------------------
|
| This method returns a list of characters online
|
| @Retrun: (Array): An array of characters
|
*/     
    public function get_online_list_datatables()
    {
        $ajax = $this->load->model("Ajax_Model", "ajax");
  
        /* 
        * Dwsc: Array of database columns which should be read and sent back to DataTables. 
        * Format: id, name, character level, race ID, class ID, Gender ID, and Zone ID
        */
        $cols = array( 'guid', 'name', 'level', 'race', 'class', 'gender', 'zone' );
        
        /* Character ID column name */
        $index = "guid";
        
        /* characters table name to use */
        $table = "characters";
        
        /* add where */
        $where = '`online` = 1';
        
        /* Process the request */
        return $ajax->process_datatables($cols, $index, $table, $where, $this->DB);
    }
    
/*
| ---------------------------------------------------------------
| Method: get_character_list_datatables
| ---------------------------------------------------------------
|
| This method returns a list of characters

| @Retrun: (Array): An array of characters
|
*/     
    public function get_character_list_datatables()
    {
        $ajax = $this->load->model("Ajax_Model", "ajax");
  
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
        $where = '';
        
        /* Process the request */
        return $ajax->process_datatables($cols, $index, $table, $where, $this->DB);
    }

/*
| ---------------------------------------------------------------
| Method: get_faction_top_kills
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
    function get_faction_top_kills($faction, $limit, $start)
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
| Method: set_character_info
| ---------------------------------------------------------------
|
| This method is used to set an array of character information
|
| @Param: (Int) $id - The character ID
| @Param:(Array): $info - an array of fields to set... This includes
| these fields (NOTE: you need not set all of these, just the ones
|   you are updating)
|   array(
|       'account' => The account ID the character belongs too
|       'name' => The characters name
|       'gender' => Gender
|       'level' => Level
|       'money' => characters money
|       'xp' => Characters current level expierience
|   );
|
*/  
    public function set_character_info($id, $info)
    {
        // First we check to make sure the character exists!
        $query = "SELECT `account`, `name`, `gender`, `level`, `money`, `xp` FROM `characters` WHERE `guid`=?";
        $char = $this->DB->query( $query, array($id) )->fetch_row();
        if($char === false)
        {
            // Character doesnt exist or is online
            return false;
        }
        else
        {
            // If the name changed, check to make sure a different char doesnt have that name
            if(isset($info['name']))
            {
                if($char['name'] != $info['name'])
                {
                    if($this->character_name_exists($info['name'])) return false;
                }
            }
            
            // Build our data array ( 'column_name' => $info['infoid'] )
            // We need to check if each field is set, if not, use $char default
            $data = array(
                'account'   => (isset($info['account'])) ? $info['account'] : $char['account'],
                'name'      => (isset($info['name']))    ? $info['name']    : $char['name'],
                'gender'    => (isset($info['gender']))  ? $info['gender']  : $char['gender'],
                'level'     => (isset($info['level']))   ? $info['level']   : $char['level'],
                'money'     => (isset($info['money']))   ? $info['money']   : $char['money'],
                'xp'        => (isset($info['xp']))      ? $info['xp']      : $char['xp']
            );
            
            // Update the 'characters' table, SET 'name' => $new_name WHERE guid(id) => $id
            return $this->DB->update('characters', $data, "`guid`=".$id);
        }
    }

/*
| ---------------------------------------------------------------
| Method: set_character_level
| ---------------------------------------------------------------
|
| This method is used to set a characters level
|
| @Param: (Int) $id - The character id we are updating
| @Param: (Int) $new_level - The characters new level
| @Retrun: (Bool): True on success, FALSE otherwise
|
*/    
    public function set_character_level($id, $new_level)
    {
        // First we check to make sure the character exists!
        $query = "SELECT `online` FROM `characters` WHERE `guid`=?";
        $online = $this->DB->query( $query, array($id) )->fetch_column();
        if($online === FALSE)
        {
            // Character doesnt exist if we get a staight up FALSE
            return FALSE;
        }

        // Update the 'characters' table, SET 'level' => $new_level WHERE guid(id) => $id
        return $this->DB->update('characters', array('level' => $new_level), "`guid`=".$id);
    }
    
/*
| ---------------------------------------------------------------
| Method: set_character_name
| ---------------------------------------------------------------
|
| This method is used to set a characters name
|
| @Param: (Int) $id - The character id we are updating
| @Param: (Int) $new_name - The characters new name
| @Retrun: (Bool): True on success, FALSE otherwise
|
*/    
    public function set_character_name($id, $new_name)
    {
        // First we check to make sure the character exists!
        $query = "SELECT `online` FROM `characters` WHERE `guid`=?";
        $online = $this->DB->query( $query, array($id) )->fetch_column();
        if($online === FALSE)
        {
            // Character doesnt exist if we get a staight up FALSE
            return FALSE;
        }
        
        // Update the 'characters' table, SET 'name' => $new_name WHERE guid(id) => $id
        return $this->DB->update('characters', array('level' => $new_level), "`guid`=".$id);
    }
    
/*
| ---------------------------------------------------------------
| Method: set_character_name
| ---------------------------------------------------------------
|
| This method is used to set a characters account ID
|
| @Param: (Int) $id - The character id we are updating
| @Param: (Int) $account - The new account id
| @Retrun: (Bool): True on success, FALSE otherwise
|
*/    
    public function set_character_account_id($id, $account)
    {
        // First we check to make sure the character exists!
        $query = "SELECT `online` FROM `characters` WHERE `guid`=?";
        $online = $this->DB->query( $query, array($id) )->fetch_column();
        if($online === FALSE)
        {
            // Character doesnt exist if we get a staight up FALSE
            return FALSE;
        }

        // Update the 'characters' table, SET 'account' => $new_account WHERE guid(id) => $id
        return $this->DB->update('characters', array('account' => $account), "`guid`=".$id);
    }
    
/*
| ---------------------------------------------------------------
| Method: adjust_character_level
| ---------------------------------------------------------------
|
| This method is used to adjust a characters level by the $mod
|
| @Param: (Int) $id - The character id we are updating
| @Param: (Int) $mod - The characters modification amount to level
| @Retrun: (Bool): True on success, FALSE otherwise
|
*/
    public function adjust_character_level($id, $mod)
    {
        // First we check to make sure the character exists!
        $query = "SELECT `level` FROM `characters` WHERE `guid`=?";
        $lvl = $this->DB->query( $query, array($id) )->fetch_column();
        if($lvl == FALSE)
        {
            return FALSE;
        }
        else
        {
            // Adjust the level
            $newlvl = $lvl + $mod;

            // Update the 'characters' table, SET 'level' => $new_level WHERE guid(id) => $id
            return $this->DB->update('characters', array('level' => $new_level), "`guid`=".$id);
        }
    }

/*
| ---------------------------------------------------------------
| Method: adjust_character_gold
| ---------------------------------------------------------------
|
| This method is used to adjust a characters gold by the $mod
|
| @Param: (Int) $id - The character id we are updating
| @Param: (Int) $mod - The characters modification amount to gold
| @Retrun: (Bool): True on success, FALSE otherwise
|
*/ 
    public function adjust_character_gold($id, $mod)
    {
        // First we check to make sure the character exists!
        $query = "SELECT `money` FROM `characters` WHERE `guid`=?";
        $gold = $this->DB->query( $query, array($id) )->fetch_column();
        if($gold == FALSE)
        {
            return FALSE;
        }
        else
        {
            // Adjust the gold
            $new = $gold + $mod;

            // Update the 'characters' table, SET 'level' => $new_level WHERE guid(id) => $id
            return $this->DB->update('characters', array('money' => $new), "`guid`=".$id);
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: delete_character
| ---------------------------------------------------------------
|
| This method removes the character from the characters DB
|
| @Param: (Int) $id - The character id we are deleteing
| @Retrun: (Bool): True on success, FALSE otherwise
|
*/ 
    public function delete_character($id)
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
    
    public function reset_poistion($id){}



/*
| -------------------------------------------------------------------------------------------------
|                               AT LOGIN FLAGS
| -------------------------------------------------------------------------------------------------
*/


/*
| ---------------------------------------------------------------
| Method: login_flags()
| ---------------------------------------------------------------
|
| This method is used to return a list of "at login" flags this
| core / revision is able to do. Please note, the functions must
| exist!
|
| @Retrun: (Array): An array of true / false flags
|
*/ 
    public function login_flags()
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
| Method: flag_to_bit()
| ---------------------------------------------------------------
|
| This method is used to return the bitmask flag for the givin flag 
| name
|
| @Param: (String) $flag - The flag name we are getting the bit for
| @Retrun: (Int | Bool): The bitmask on success, False otherwise
|
*/
    public function flag_to_bit($flag)
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
| ---------------------------------------------------------------
| Method: set_login_flag()
| ---------------------------------------------------------------
|
| This method is used to return a list of "at login" flags this
| core / revision is able to do. Please note, the functions must
| exist!
|
| @Param: (Int) $id - The character id
| @Param: (String) $name - The flag name we are settings
| @Param: (Bool) $status - True to enable flag, false to remove it
| @Retrun: (Bool): True on success, False otherwise
|
*/ 
    public function set_login_flag($id, $name, $status)
    {
        // First, get current login flags
        $query = "SELECT `at_login` FROM `characters` WHERE `guid`=?";
        $flags = $this->DB->query( $query, array($id) )->fetch_column();
        
        // Make sure we didnt get a false return!
        if( $flags === false ) return false;
        
        // Convert flags to an int, and get our bit id
        $flags  = (int) $flags;
        $flagid = (int) $this->flag_to_bit($name);
        
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
        
        // Update the database setting the new flag
        return $this->DB->update('characters', array('at_login' => $newflags), "`guid`=$id");
    }
    
/*
| ---------------------------------------------------------------
| Method: has_login_flag()
| ---------------------------------------------------------------
|
| This method is used to return a if a character has the specified
| login flag enabled
|
| @Param: (Int) $id - The character id
| @Param: (String) $name - The flag name we are getting
| @Retrun: (Bool): True if the character has the flag, False otherwise
|
*/ 
    public function has_login_flag($id, $name)
    {
        // First, get current login flags
        $query = "SELECT `at_login` FROM `characters` WHERE `guid`=?";
        $flags = $this->DB->query( $query, array($id) )->fetch_column();
        
        // Is there any flags set?
        if( $flags == false ) return false;
        
        // Convert flags to an int, and get our bit id
        $flags  = (int) $flags;
        $flagid = (int) $this->flag_to_bit($name);
        
        // Make sure this feature is supported
        if($flagid == 0) return false;
        
        // Check, if the flag is set, return true
        return ($flags & $flagid) ? true : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_login_flags()
| ---------------------------------------------------------------
|
| This method is used to return all login flags the character has
|
| @Param: (Int) $id - The character id
| @Retrun: (Array): An array of true / false flags
|
*/ 
    public function get_login_flags($id)
    {
        // Build the dummy array
        $flags = array();
        $supported = $this->login_flags();
        foreach($supported as $key => $flag)
        {
            $flags[$key] = false;
        }
        
        // First, get current login flags
        $query = "SELECT `at_login` FROM `characters` WHERE `guid`=?";
        $cflags = $this->DB->query( $query, array($id) )->fetch_column();
        
        // Is there any flags set?
        if( $cflags == false ) return $flags;
        
        // Determine if each flag is true or false
        foreach($flags as $key => $flag)
        {
            $bit = $this->flag_to_bit($key);
            $flags[$key] = ($cflags & $bit) ? true : false;
        }
        
        return $flags;
    }
    
    
/*
| -------------------------------------------------------------------------------------------------
|                               HELPER FUNCTIONS
| -------------------------------------------------------------------------------------------------
*/


    public function race_to_text($id)
    {
        // Check if the race is set, if not then Unknown
        if(isset($this->info['race'][$id]))
        {
            return $this->info['race'][$id];
        }
        return "Unknown";
    }

    public function class_to_text($id)
    {
        // Check if the class is set, if not then Unknown
        if(isset($this->info['class'][$id]))
        {
            return $this->info['class'][$id];
        }
        return "Unknown";
    }

    public function gender_to_text($id)
    {
        // Check if the gender is set, if not then Unknown
        if(isset($this->info['gender'][$id]))
        {
            return $this->info['gender'][$id];
        }
        return "Unknown";
    }
}