<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Helpers/Io.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @author      Plexis Dev Team
 * @package     Helpers
 * @subpackage  IO
 */

/**
 * Combines several string arguments into a file path.
 *
 * @param string|string[] $parts The pieces of the path, passed as 
 *   individual arguments. Each argument can be a single dimmensional 
 *   array of paths, a string folder / filename, or a mixture of the two.
 *   Dots may also be passed ( . & .. ) to change directory levels
 *
 * @return string Returns the full path using the correct system 
 *   directory separater
 */
    function path($parts = null)
    {
        // Get our path parts
        $args = func_get_args();
        $parts = array();
        
        // Trim our paths to remvove spaces and new lines
        foreach($args as $part)
        {
            // If part is array, then implode and continue
            if(is_array($part))
            {
                // Remove empty entries
                $part = array_filter($part, 'strlen');
                $parts[] = implode(DS, $part);
                continue;
            }
            
            // String
            $part = trim($part);
            if($part == '.' || empty($part))
                continue;
            if($part == '..')
                array_pop($parts);
            else
                $parts[] = $part;
        }

        // Get our cleaned path into a variable with the correct directory seperator
        return implode( DS, $parts );
    }
    
/**
 * This function is to return a complete root path from a relative path.
 *
 * This function is meant to replace PHP's extremely buggy realpath().
 *
 * @param string $path The original path, can be relative. Dots may also be 
 *   passed ( . & .. ) to change directory levels. If the $path starts with a
 *   forward slash, the $path will be interpreted as "from the root folder" on
 *   Unix machines.
 *
 * @return string The resolved path without a trailing slash, it might not exist.
 */
    function truePath($path)
    {
        // If path is empty, just return ROOT path
        if(empty($path))
            return ROOT;
            
        // whether $path is unix or not
        $unipath = ($path{0} == '/');
        
        // Correct our directory seperator, and remove empty paths
        $path = str_replace(array('/', '\\'), DS, $path);
        $parts = array_filter(explode(DS, $path), 'strlen');
        $absolutes = array();
        
        // Resolve single and double dot paths
        foreach($parts as $part)
        {
            if($part == '.')
                continue;
            if($part == '..')
                array_pop($absolutes);
            else
                $absolutes[] = $part;
        }
        
        // Rebuild the full path with the Directory Seporator
        $path = implode(DS, $absolutes);
        
        // If we donot have a drive letter, or a unix path, prepend root path
        if(strpos($path, ':') === false && !$unipath)
            $path = ROOT . DS . $path;
        
        // put initial separator that could have been lost
        $path = ($unipath) ? '/'. ltrim($path, '/') : $path;
        return rtrim($path, DS);
    }
    
/**
 * Formats a file size to human readable format
 *
 * @param string|float|int The size in bytes
 *
 * @return string Returns a formatted size ( Ex: 32.6 MB )
 */
    function formatSize($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2).$units[$i];
    }
// EOF