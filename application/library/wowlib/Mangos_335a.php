<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
*/
namespace Application\Library\Wowlib;

class Mangos_335a
{
    // Our DB Connections
    protected $DB;
    protected $RDB;
    protected $CDB;
    protected $WDB;
    
    // remote access
    protected $ra_info = NULL;
    
    // Out realm and realm info arrays
    protected $realm;
    protected $realm_info;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct($realm_id)
    {
        // Load the Loader class
        $this->load = load_class('Loader');
        
        // Load the Database and Realm database connections
        $this->DB = $this->load->database( 'DB' );
        $this->RDB = $this->load->database( 'RDB' );
        $this->realm = $this->load->realm();
        
        // Get our DB info
        $query = "SELECT * FROM `pcms_realms` WHERE `id`=?";
        $realm = $this->DB->query( $query, array($realm_id))->fetch_row();
        
        // Turn our connection info into an array
        $world = explode(';', $realm['world_db']);
        $char = explode(';', $realm['char_db']);
        $ra_info = explode(';', $realm['ra_info']);
        
        // Build the world connection array
        $world = array(
            'driver' => $world[0],
            'host' => $world[1],
            'port' => $world[2],
            'username' => $world[3],
            'password' => $world[4],
            'database' => $world[5]
        );
        // Build the character conenction array
        $char = array(
            'driver' => $char[0],
            'host' => $char[1],
            'port' => $char[2],
            'username' => $char[3],
            'password' => $char[4],
            'database' => $char[5]
        );
        // Build our Remote Access data
        if(is_array($ra_info))
        {
            $this->ra_info = array(
                'type' => $ra_info[0],
                'port' => $ra_info[1],
                'user' => $ra_info[2],
                'pass' => $ra_info[3]
            );
        }
        
        // Set the connections into the connection variables
        $this->CDB = $this->load->database($char);
        $this->WDB = $this->load->database($world);
        
        // Finally set our class realm variable
        $this->realm_info = $realm;
    }
    
/*
| -------------------------------------------------------------------------------------------------
|                               CHARACTER DATABASE FUNCTIONS
| -------------------------------------------------------------------------------------------------
*/
    
/*
| ---------------------------------------------------------------
| Funtion: list_characters
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
        $query = "SELECT `guid`, `name`, `race`, `gender`, `class`, `level` FROM `characters` LIMIT ".$start.", ".$limit;
        $list = $this->CDB->query( $query )->fetch_array();
        
        // If we have a false return, then there was nothinf to select
        if($list === FALSE)
        {
            return array();
        }
        return $list;
    }
    
/*
| ---------------------------------------------------------------
| Funtion: character_online
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
        $online = $this->CDB->query( $query, array($id) )->fetch_column();
        if($online == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, we have the characters status
        return $online;
    }
    
/*
| ---------------------------------------------------------------
| Funtion: character_name_exists
| ---------------------------------------------------------------
|
| This method is used to determine if a character name is available
|
| @Param: (Int) $name - The character name we are looking up
| @Retrun:(Bool): TRUE if the name is available, FALSE otherwise
|
*/     
    public function character_name_exists($name)
    {
        // Build our query
        $query = "SELECT `id` FROM `characters` WHERE `name`=?";
        $exists = $this->CDB->query( $query, array($name) )->fetch_column();
        if($exists == FALSE)
        {
            return TRUE;
        }
        
        // If we are here, the name is unavailable
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Funtion: get_character_account_id
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
        $account = $this->CDB->query( $query, array($id) )->fetch_column();
        if($account == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the account ID. return it
        return $account;
    }
    
/*
| ---------------------------------------------------------------
| Funtion: get_character_name
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
        $result = $this->CDB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the name. return it
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Funtion: get_character_level
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
        $result = $this->CDB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the result. return it
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Funtion: get_character_race
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
        $result = $this->CDB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the result. return it
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Funtion: get_character_class
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
        $result = $this->CDB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the result. return it
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Funtion: get_character_gender
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
        $result = $this->CDB->query( $query, array($id) )->fetch_column();
        if($result == FALSE)
        {
            return FALSE;
        }
        
        // If we are here, then we have the result. return it
        return $result;
    }

/*
| ---------------------------------------------------------------
| Funtion: get_character_faction
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
| Funtion: get_character_gold
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
        $gold = $this->CDB->query( $query, array($id) )->fetch_column();
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
| Funtion: get_online_count
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
        return $this->CDB->query( $query )->fetch_column();
    }

/*
| ---------------------------------------------------------------
| Funtion: get_online_list
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
        return $this->CDB->query( $query )->fetch_array();
    }

/*
| ---------------------------------------------------------------
| Funtion: get_faction_top_kills
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
        return $this->CDB->query( $query )->fetch_array();
	}

/*
| ---------------------------------------------------------------
| Funtion: set_character_level
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
        $online = $this->CDB->query( $query, array($id) )->fetch_column();
        if($online === FALSE)
        {
            // Character doesnt exist if we get a staight up FALSE
            return FALSE;
        }
        elseif($online == 1)
        {
            // Cant change an online players leve
            return FALSE;
        }
        else
        {
            // Update the 'characters' table, SET 'level' => $new_level WHERE guid(id) => $id
            return $this->CDB->update('characters', array('level' => $new_level), "`guid`=".$id);
        }
    }
    
/*
| ---------------------------------------------------------------
| Funtion: set_character_name
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
        $online = $this->CDB->query( $query, array($id) )->fetch_column();
        if($online === FALSE)
        {
            // Character doesnt exist if we get a staight up FALSE
            return FALSE;
        }
        elseif($online == 1)
        {
            // Cant change an online players name
            return FALSE;
        }
        else
        {
            // Update the 'characters' table, SET 'name' => $new_name WHERE guid(id) => $id
            return $this->CDB->update('characters', array('level' => $new_level), "`guid`=".$id);
        }
    }
    
/*
| ---------------------------------------------------------------
| Funtion: set_character_name
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
        $online = $this->CDB->query( $query, array($id) )->fetch_column();
        if($online === FALSE)
        {
            // Character doesnt exist if we get a staight up FALSE
            return FALSE;
        }
        elseif($online == 1)
        {
            // Cant change an online players qccount
            return FALSE;
        }
        else
        {
            // Update the 'characters' table, SET 'name' => $new_name WHERE guid(id) => $id
            return $this->CDB->update('characters', array('account' => $account), "`guid`=".$id);
        }
    }
    
/*
| ---------------------------------------------------------------
| Funtion: adjust_character_level
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
        $lvl = $this->CDB->query( $query, array($id) )->fetch_column();
        if($lvl == FALSE)
        {
            return FALSE;
        }
        else
        {
            // Adjust the level
            $newlvl = $lvl + $mod;

            // Update the 'characters' table, SET 'level' => $new_level WHERE guid(id) => $id
            return $this->CDB->update('characters', array('level' => $new_level), "`guid`=".$id);
        }
    }

/*
| ---------------------------------------------------------------
| Funtion: adjust_character_gold
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
        $gold = $this->CDB->query( $query, array($id) )->fetch_column();
        if($gold == FALSE)
        {
            return FALSE;
        }
        else
        {
            // Adjust the gold
            $new = $gold + $mod;

            // Update the 'characters' table, SET 'level' => $new_level WHERE guid(id) => $id
            return $this->CDB->update('characters', array('money' => $new), "`guid`=".$id);
        }
    }
    
    
/*
| -------------------------------------------------------------------------------------------------
|                               WORLD DATABASE FUNCTIONS
| -------------------------------------------------------------------------------------------------
*/
}
?>