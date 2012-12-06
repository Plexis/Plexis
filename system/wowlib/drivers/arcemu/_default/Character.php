<?php
/* 
| -------------------------------------------------------------- 
| Character Object Class
| --------------------------------------------------------------
|
| Author:       Wilson212
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/

// All namespace paths must be Uppercase first letter! Format: "Wowlib\<Emulator>\<Wowlib_name>"
namespace Wowlib\Arcemu\_default;

class Character extends \Wowlib\Character
{
    // Our DB Connection and Characters parent class
    protected $DB;
    protected $parent;
    
    // Our character variables, and columns
    protected $data = array();
    protected $cols;
    
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
    public function __construct($data, $parent, $cols)
    {
        // Make sure we didnt get a false return
        if(!is_array($data)) throw new \Exception('Character doesnt exist');
        
        // Set oru database conntection, which is passed when this class is Init.
        $this->DB = $parent->getDB();
        $this->parent = $parent;
        $this->data = $data;
        $this->cols = $cols;
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
            $query = "SELECT `entry`, `slot` FROM `playeritems` WHERE `ownerguid`={$this->data['guid']} AND `containerslot`='-1' AND `slot` < 19;";
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
        // Get positions
        $query = "SELECT `bindpositionX`, `bindpositionY`, `bindpositionZ`, `bindmapId` FROM `characters` WHERE `guid`= ?";
        $data = $this->DB->query($query, $this->data['guid'])->fetchRow();
        
        // Set the position
        return $this->setPosition($data['bindpositionX'], $data['bindpositionY'], $data['bindpositionZ'], $data['orientation'], $data['bindmapId']);
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
}