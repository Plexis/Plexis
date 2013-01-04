<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Config.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Config
 */
namespace Core;

/**
 * Main Config class. Used to load, set, and save variables used
 * in the config file.
 *
 * @author  Steven Wilson 
 * @package Core
 */
class Config
{
    /**
     * An array of all out stored containers / variables
     * @var mixed[]
     */
    protected static $data = array();
    
    /**
     * A list of our loaded config files
     * @var array[]
     */
    protected static $files = array();
    
    /**
     * Returns the variable ($key) value in a config file.
     *
     * @param string $key Config variable name
     * @param string $name Config id or name given when loaded
     * @return mixed May return NULL if the var is not set
     */
    public static function GetVar($key, $name) 
    {
        // Lowercase the type
        $name = strtolower($name);
        
        // Check if the variable exists
        return (isset(self::$data[$name][$key])) ? self::$data[$name][$key] : NULL;
    }
    
    /**
     * Returns all variables in an array from the the config file.
     *
     * @param string $name Config id or name given when loaded
     * @return array|null Array of variables ($key => $value). 
     * May return NULL if the var is not set
     */
    public static function FetchVars($name) 
    {
        // Lowercase the type
        $name = strtolower($name);
        
        // Check if the variable exists
        return (isset(self::$data[$name])) ? self::$data[$name] : NULL;
    }
    
    /**
     * Sets the variable ($key) value. If not saved, default value
     * will be returned as soon as page is re-loaded / changed.
     *
     * @param string|array $key Config variable name to be set, or an
     * array of $key => $value
     * @param string $val The config variable's new value
     * @param string $name Config id or name given when loaded
     * @return bool Returns false if the config file denies set perms
     */
    public static function SetVar($key, $val = false, $name) 
    {
        // Lowercase the $name
        $name = strtolower($name);
        
        // If we have array, loop through and set each
        if(is_array($key))
        {
            foreach($key as $k => $v)
            {
                self::$data[$name][$k] = $v;
            }
        }
        else
        {
            self::$data[$name][$key] = $val;
        }
        
        return true;
    }
    
    /**
     * Loads a config file, and adds its defined variables to the $data array
     *
     * @param string $_Cfile Full path to the config file, includeing name
     * @param string $_Cname The container name we are storing this configs
     * @param string $_Carray If all of the config vars are stored in an array, 
     *    whats the array variable name?
     *
     * @throws \FileNotFoundException if the config file does not exist
     *
     * @return bool Returns false if the config file cannot be found or read
     */
    public static function Load($_Cfile, $_Cname, $_Carray = false) 
    {
        // Lowercase the $name
        $_Cname = strtolower($_Cname);
        
        // Donot load the config twice!
        if(array_key_exists($_Cname, self::$files))
            return true;
        
        // Add trace for debugging
        // \Debug::trace('Loading config "'. $_name .'" from: '. $_file, __FILE__, __LINE__);
        
        // Include file and add it to the $files array
        if(!file_exists($_Cfile)) 
            throw new \FileNotFoundException("Config file '{$_Cfile}' does not exist!");
        include( $_Cfile );
        
        // Set config file flags
        self::$files[$_Cname]['file_path'] = $_Cfile;
        self::$files[$_Cname]['config_key'] = $_Carray;
        
        // Get defined variables
        $vars = get_defined_vars();
        if($_Carray != false) 
            $vars = $vars[$_Carray];
        else
            // Unset the passes vars
            unset($vars['_Cfile'], $vars['_Cname'], $vars['_Carray']);
        
        // Add the variables to the $data[$name] array
        if(count($vars) > 0)
        {
            foreach( $vars as $key => $val ) 
            {
                if($key != 'this' && $key != 'data') 
                {
                    self::$data[$_Cname][$key] = $val;
                }
            }
        }
        
        return true;
    }
    
    /**
     * This method is used to unload a config
     *
     * @param string $name Config id or name given when loaded
     * @return void
     */
    public static function UnLoad($name) 
    {
        unset(self::$data[$name]);
    }
    
    /**
     * This method returns if a config name is loaded
     *
     * @param string $name Config id or name given when loaded
     * @return bool
     */
	public static function IsLoaded($name)
	{
		return array_key_exists($name, self::$data);
	}
    
    /**
     * Saves all set config variables to the config file, and makes 
     * a backup of the current config file
     *
     * @param string $name Config id or name given when loaded
     * @return bool true on success, false otherwise
     */ 
    public static function Save($name) 
    {
        // Lowercase the $name
        $name = strtolower($name);
        
        // Add trace for debugging
        // \Debug::trace('Saving config: '. $name, __FILE__, __LINE__);
        
        // Check to see if we need to put this in an array
        $ckey = self::$files[$name]['config_key'];
        if($ckey != false)
        {
            $Old_Data = self::$data[$name];
            self::$data[$name] = array("$ckey" => self::$data[$name]);
        }

        // Create our new file content
        $cfg  = "<?php\n";

        // Loop through each var and write it
        foreach( self::$data[$name] as $key => $val )
        {
            switch( gettype($val) )
            {
                case "boolean":
                    $val = ($val == true) ? 'true' : 'false';
                    // donot break
                case "integer":
                case "double":
                case "float":
                    $cfg .= "\$$key = " . $val . ";\n";
                    break;
                case "array":
                    $val = var_export($val, true);
                    $cfg .= "\$$key = " . $val . ";\n";
                    break;
                case "NULL":
                    $cfg .= "\$$key = null;\n";
                    break;
                case "string":
                    $cfg .= (is_numeric($val)) ? "\$$key = " . $val . ";\n" : "\$$key = '" . addslashes( $val ) . "';\n";
                    break;
                default: break;
            }
        }

        // Close the php tag
        $cfg .= "?>";
        
        // Add the back to non array if we did put it in one
        if($ckey != false) self::$data[$name] = $Old_Data;
        
        // Copy the current config file for backup, 
        // and write the new config values to the new config
        copy(self::$files[$name]['file_path'], self::$files[$name]['file_path'].'.bak');
        if(file_put_contents( self::$files[$name]['file_path'], $cfg )) 
        {
            // Add trace for debugging
            // \Debug::trace('Successfully Saved config: '. $name, __FILE__, __LINE__);
            return true;
        } 
        else 
        {
            // Add trace for debugging
            // \Debug::trace('Failed to save config: '. $name, __FILE__, __LINE__);
            return false;
        }
    }
    
    /**
     * This method is used to undo the last Save. .bak file must be
     * in the config folder
     *
     * @param string $name Config id or name given when loaded
     * @return bool true on success, false otherwise
     */ 
    public static function Restore($name) 
    {
        // Copy the backup config file nd write the config values to the current config
        return copy(self::$files[$name]['file_path'].'bak', self::$files[$name]['file_path']);
    }
}
// EOF