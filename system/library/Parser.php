<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Library/Parser.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Parser
 */
namespace Library;

/**
 * A source parsing class
 *
 * @author      Steven Wilson 
 * @package     Library
 */
class Parser
{
    /**
     * Left parsing delimiter
     * @var string
     */
    protected static $l_delim = '{';
    
    /**
     * Right parsing delimeter
     * @var string
     */
    protected static $r_delim = '}';
    
    /**
     * Sets the template delimiters for psuedo blocks
     *
     * @param string $l The left delimiter
     * @param string $r The right delimiter
     *
     * @return void
     */
    public static function SetDelimiters($l = '{', $r = '}')
    {
        self::$l_delim = $l;
        self::$r_delim = $r;
    }
    
    /**
     * This method uses all defined template assigned variables
     * to loop through and replace the Psuedo blocks that contain
     * variable names
     *
     * @param string $source The source with all the {variables}
     * @param mixed[] $data Array of variables to be parsed
     *
     * @return string The parsed contents are returned
     */
    public static function Parse($source, $data)
    {
        // store the vars into $data, as its easier then $this->variables
        $replaced_something = true;
        $count = 0;
        
        // Do a search and destroy or psuedo blocks... keep going till we replace everything
        while($replaced_something == true)
        {
            // Our loop stopers
            $replaced_something = false;
            
            // Make sure we arent endlessly looping :O
            if($count > 5)
            {
                show_error('parser_endless_loop', false, E_WARNING);
                break;
            }
            
            // Loop through the data and catch arrays
            foreach($data as $key => $value)
            {
                // If $value is an array, we need to process it as so
                if(is_array($value))
                {
                    // First, we check for array blocks (Foreach blocks), you do so by checking: {/key} 
                    // .. if one exists we preg_match the block
                    if(strpos($source, self::$l_delim . '/' . $key . self::$r_delim) !== false)
                    {
                        // Create our array block regex
                        $regex = self::$l_delim . $key . self::$r_delim . "(.*)". self::$l_delim . '/' . $key . self::$r_delim;
                        
                        // Match all of our array blocks into an array, and parse each individually
                        preg_match_all("~" . $regex . "~iUs", $source, $matches, PREG_SET_ORDER);
                        foreach($matches as $match)
                        {
                            // Parse pair: Source, Match to be replaced, With what are we replacing?
                            $replacement = self::ParsePair($match[1], $value);
                            
                            // Check for a parser false
                            if($replacement === "_PARSER_false_") continue;
                            
                            // Main replacement
                            $source = str_replace($match[0], $replacement, $source);
                            $replaced_something = true;
                        }
                    }
                    
                    // Now that we are done checking for blocks, Create our array key indentifier
                    $key = $key .".";
                    
                    // Next, we check for nested array blocks, you do so by checking for: {/key.*}.
                    // ..if one exists we preg_match the block
                    if(strpos($source, self::$l_delim . "/" . $key) !== false)
                    {
                        // Create our regex
                        $regex = self::$l_delim . $key ."(.*)". self::$r_delim . "(.*)". self::$l_delim . '/' . $key ."(.*)". self::$r_delim;
                        
                        // Match all of our array blocks into an array, and parse each individually
                        preg_match_all("~" . $regex . "~iUs", $source, $matches, PREG_SET_ORDER);
                        foreach($matches as $match)
                        {
                            // process the array
                            $array = self::ParseArray($match[1], $value);

                            // Parse pair: Source, Match to be replaced, With what are we replacing?
                            $replacement = self::ParsePair($match[2], $array);
                            
                            // Check for a parser false
                            if($replacement === "_PARSER_false_") continue;
                            
                            // Check for a false reading
                            $source = str_replace($match[0], $replacement, $source);
                            $replaced_something = true;
                        }
                    }

                    // Lastley, we check just plain arrays. We do this by looking for: {key.*} 
                    // .. if one exists we preg_match the array
                    if(strpos($source, self::$l_delim . $key) !== false)
                    {
                        // Create our regex
                        $regex = self::$l_delim . $key . "(.*)".self::$r_delim;
                        
                        // Match all of our arrays into an array, and parse each individually
                        preg_match_all("~" . $regex . "~iUs", $source, $matches, PREG_SET_ORDER);
                        foreach($matches as $match)
                        {
                            // process the array
                            $replacement = self::ParseArray($match[1], $value);
                            
                            // If we got a false array parse, then skip the rest of this loop
                            if($replacement === "_PARSER_false_") continue;
                            
                            // If our replacement is a array, it will cause an error, so just return "array"
                            if(is_array($replacement)) $replacement = "array";
                            
                            // Main replacement
                            $source = str_replace($match[0], $replacement, $source);
                            
                            // If we are putting the match back to an array key, we will cause an endless loop
                            if($replacement != $match[0]) $replaced_something = true;
                        }
                    }
                }
            }
            
            // Now parse singles. We do this last to catch variables that were
            // inside array blocks...
            foreach($data as $key => $value)
            {
                // We dont handle arrays here
                if(is_array($value)) continue;
                
                // Find a match for our key, and replace it with value
                $match = self::$l_delim . $key . self::$r_delim;
                if(strpos($source, $match) !== false)
                {
                    $source = str_replace($match, $value, $source);
                    $replaced_something = true;
                }
            }
            
            // Raise the counter
            ++$count;
        }
        
        // Return the parsed source
        return $source;
    }
    
    /**
     * Parses an array such as {user.userinfo.username}
     *
     * @param string $key The full unparsed array ( { something.else} )
     * @param mixed[] $array The actual array that holds the value of $key
     *
     * @return string Returns the parsed value of the array key
     */
    public static function ParseArray($key, $array)
    {
        // Check to see if this is even an array first
        if(!is_array($array)) return $array;

        // Check if this is a multi-dimensional array
        if(strpos($key, '.') !== false)
        {
            $args = explode('.', $key);
            $count = count($args);
            $last = $count - 1;
            $s_key = '';
            
            // Loop though each level (period or "element")
            for($i = 0; $i < $last; $i++)
            {
                // add quotes if the argument is a string
                if(!is_numeric($args[$i]))
                {
                    $s_key .= "['$args[$i]']";
                }
                else
                {
                    $s_key .= "[$args[$i]]";
                }
            }
            
            // Check if variable exists in $val
            return eval('if(array_key_exists($args[$last], $array'. $s_key .')) return $array'. $s_key .'[$args[$last]]; return "_PARSER_false_";');
        }
        
        // Just a simple 1 stack array
        else
        {
            // Check if variable exists in $array
            if(array_key_exists($key, $array))
            {
                return $array[$key];
            }
        }
        
        // Tell the requester that the array doesnt exist
        return "_PARSER_false_";
    }
    
    /**
     * Parses array blocks (  {key} ,,, {/key} ), acts like a foreach loop
     *
     * @param string $match The preg_match of the block {key} (what we need) {/key}
     * @param mixed[] $val The array that contains the variables inside the blocks
     *
     * @return string Returns the parsed foreach loop block
     */
    public static function ParsePair($match, $val)
    {	
        // Init the emtpy main block replacment
        $final_out = '';
        
        // Make sure we are dealing with an array!
        if(!is_array($val) || !is_string($match)) return "_PARSER_false_";
        
        // Remove nested vars, nested vars are for outside vars
        if(strpos($match, self::$l_delim . self::$l_delim) !== false)
        {
            $match = str_replace(self::$l_delim . self::$l_delim, "<<!", $match);
            $match = str_replace(self::$r_delim . self::$r_delim, "!>>", $match);
        }
        
        // Define out loop number
        $i = 0;
        
        // Process the block loop here, We need to process each array $val
        foreach($val as $key => $value)
        {
            // if value isnt an array, then we just replace {value} with string
            if(is_array($value))
            {
                // Parse our block. This will catch nested blocks and arrays as well
                $block = self::Parse($match, $value);
            }
            else
            {
                // Just replace {value}, as we are dealing with a string
                $block = str_replace('{value}', $value, $match);
            }
            
            // Setup a few variables to tell what loop number we are on
            if(strpos($block, "{loop.") !== false)
            {
                $block = str_replace("{loop.key}", $key, $block);
                $block = str_replace("{loop.num}", $i, $block);
                $block = str_replace("{loop.count}", ($i + 1), $block);
            }
            
            // Add this finished block to the final return
            $final_out .= $block;
            ++$i;
        }
        
        // Return nested vars
        if(strpos($final_out, "<<!") !== false)
        {
            $final_out = str_replace("<<!", self::$l_delim, $final_out);
            $final_out = str_replace("!>>", self::$r_delim, $final_out);
        }
        return $final_out;
    }
}
// EOF