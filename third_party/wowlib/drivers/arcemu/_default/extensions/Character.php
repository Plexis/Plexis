<?php
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

// All namespace paths must be Uppercase first letter! Format: "Wowlib\<Emulator>\<Wowlib_name>"
namespace Wowlib\Arcemu\_default;

class Character implements \Wowlib\iCharacter
{
    // Our DB Connection and Characters parent class
    protected $DB;
    protected $parent;
    
    // Our character variables
    protected $guid;
    protected $data = array();
    
    // Equiped items variables
    protected $fetchedEquippedItems = false;
    protected $equipped = array(
        'head' => 0,
        'neck' => 0,
        'shoulders' => 0,
        'body' => 0,
        'chest' => 0,
        'waist' => 0,
        'legs' => 0,
        'feet' => 0,
        'wrists' => 0,
        'hands' => 0,
        'finger1' => 0,
        'finger2' => 0,
        'trinket1' => 0,
        'trinket2' => 0,
        'back' => 0,
        'mainhand' => 0,
        'offhand' => 0,
        'ranged' => 0,
        'tabard' => 0
    );
    
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
            `acct`, 
            `name`, 
            `race`, 
            `class`, 
            `gender`, 
            `level`, 
            `xp`, 
            `gold`, 
            `positionX`, 
            `positionY`, 
            `positionZ`, 
            `mapId`, 
            `orientation`,
            `online`,
            `playedtime`,
            `forced_rename_pending`,
            `zoneId`,
            `arenaPoints`,
            `honorPoints`,
            `killsLifeTime`,
            `bindpositionX`,
            `bindpositionY`,
            `bindpositionZ`,
            `bindmapId`,
            FROM `characters` WHERE `guid`= $guid;";
        $this->data = $this->DB->query($query)->fetchRow();
        
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
        return (int) $this->data['acct'];
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
        return (int) $this->data['gold'];
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
            'x' => $this->data['positionX'],
            'y' => $this->data['positionY'],
            'z' => $this->data['positionZ'],
            'orientation' => $this->data['orientation'],
            'map' => $this->data['mapId'],
            'zone' => $this->data['zoneId']
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
        $timePlayed = explode( " ", $this->data["playedtime"] );
        return ((int) $timePlayed[0]); //Total time played.
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
        return (int) $this->data['killsLifeTime'];
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
        return (int) $this->data['honorPoints'];
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
| Method: getEquippedItems
| ---------------------------------------------------------------
|
| This method returns the characters equipped items in an array
|
| @Retrun: (Array)
|
*/
    public function getEquippedItems()
    {
        // Check if we have fetched this character items or not
        if(!$this->fetchedEquippedItems)
        {
            $query = "SELECT `entry`, `slot` FROM `playeritems` WHERE `ownerguid`={$this->guid} AND `containerslot`='-1' AND `slot` < 19;";
            $items = $this->DB->query($query)->fetchAll();

            // Add each item to the $equipped array
            if(is_array($items))
            {
                foreach($items as $item)
                {
                    $key = $this->getSlotKeyById($item['slot']);
                    $this->equipped[$key] = (int) $item['entry'];
                }
            }
            
            // Prevent future queries
            $this->fetchedEquippedItems = true;
        }
        
        return $this->equipped;
    }
    
/*
| ---------------------------------------------------------------
| Method: getSlotKeyById
| ---------------------------------------------------------------
|
| This method is a private method used to convert a slot ID from
| the database, into an array key for that slot
|
| @Retrun: (String)
|
*/
    protected function getSlotKeyById($id)
    {
        switch($id)
        {
            case 0: return 'head';
            case 1: return 'neck';
            case 2: return 'shoulders';
            case 3: return 'body';
            case 4: return 'chest';
            case 5: return 'waist';
            case 6: return 'legs';
            case 7: return 'feet';
            case 8: return 'wrists';
            case 9: return 'hands';
            case 10: return 'finger1';
            case 11: return 'finger2';
            case 12: return 'trinket1';
            case 13: return 'trinket2';
            case 14: return 'back';
            case 15: return 'mainhand';
            case 16: return 'offhand';
            case 17: return 'ranged';
            case 18: return 'tabard';
        }
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
        $cflags = (int)$this->data['forced_rename_pending'];
        if( $cflags == 0 ) return $flags;
        
        // Determine if each flag is true or false
        foreach($flags as $key => $flag)
        {
            if( $key == "rename" )
                $flags[$key] = ($cflags === 1) ? true : false;
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
        $renameFlag = $this->data["forced_rename_pending"];
        
        if( $name == "rename" )
            return ( $renameFlag === 1 ) ? true : false;
        else return false;
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
    public function resetPosition()
    {
        // Set the position
        return $this->setPosition($this->data['bindpositionX'], $this->data['bindpositionY'], $this->data['bindpositionZ'], $this->data['orientation'], $this->data['bindmapId']);
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
    public function setPosition($x, $y, $z, $o, $map)
    {
        $this->data['positionX'] = (float) $x;
        $this->data['positionY'] = (float) $y;
        $this->data['positionZ'] = (float) $z;
        $this->data['orientation'] = (float) $o;
        $this->data['mapId'] = (int) $map;
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
        // Make sure this feature is supported
        if($name != "rename") return false;
        
        $this->data["forced_rename_pending"] = ($status) ? 1 : 0;
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
        $this->data['acct'] = (int) $id;
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
        $this->data['gold'] = (int) $money;
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
        $this->data['killsLifeTime'] = (int) $kills;
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
        $this->data['honorPoints'] = (int) $points;
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