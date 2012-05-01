<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Parser
| ---------------------------------------------------------------
|
| A simple source parser
|
*/
namespace System\Library;

class Parser
{
    // Our variable Delimiters
    protected $l_delim = '{';
    protected $r_delim = '}';

/*
| ---------------------------------------------------------------
| Function: set_delimiters()
| ---------------------------------------------------------------
|
| Sets the template delimiters for psuedo blocks
|
| @Param: $l - The left delimiter
| @Param: $r - The right delimiter
|
*/
    public function set_delimiters($l = '{', $r = '}')
    {
        $this->l_delim = $l;
        $this->r_delim = $r;
    }

/*
| ---------------------------------------------------------------
| Function: parse()
| ---------------------------------------------------------------
|
| This method uses all defined template assigned variables
| to loop through and replace the Psuedo blocks that contain
| variable names
|
| @Param: $source - The source with all the {variables}
| @Param: $data - The array of variables
| @Return (String) The parsed page
|
*/
    public function parse($source, $data)
    {
        // store the vars into $data, as its easier then $this->variables
        $replaced_something = TRUE;
        $count = 0;
        
        // Do a search and destroy or psuedo blocks... keep going till we replace everything
        while($replaced_something == TRUE)
        {
            // Our loop stopers
            $replaced_something = FALSE;
            
            // Make sure we arent endlessly looping :O
            if($count > 5)
            {
                show_error('parser_endless_loop', FALSE, E_WARNING);
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
                    if(strpos($source, $this->l_delim . '/' . $key . $this->r_delim) !== FALSE)
                    {
                        // Create our array block regex
                        $regex = $this->l_delim . $key . $this->r_delim . "(.*)". $this->l_delim . '/' . $key . $this->r_delim;
                        
                        // Match all of our array blocks into an array, and parse each individually
                        preg_match_all("~" . $regex . "~iUs", $source, $matches, PREG_SET_ORDER);
                        foreach($matches as $match)
                        {
                            // Parse pair: Source, Match to be replaced, With what are we replacing?
                            $replacement = $this->parse_pair($match[1], $value);
                            
                            // Check for a parser false
                            if($replacement === "_PARSER_FALSE_") continue;
                            
                            // Main replacement
                            $source = str_replace($match[0], $replacement, $source);
                            $replaced_something = TRUE;
                        }
                    }
                    
                    // Now that we are done checking for blocks, Create our array key indentifier
                    $key = $key .".";
                    
                    // Next, we check for nested array blocks, you do so by checking for: {/key.*}.
                    // ..if one exists we preg_match the block
                    if(strpos($source, $this->l_delim . "/" . $key) !== FALSE)
                    {
                        // Create our regex
                        $regex = $this->l_delim . $key ."(.*)". $this->r_delim . "(.*)". $this->l_delim . '/' . $key ."(.*)". $this->r_delim;
                        
                        // Match all of our array blocks into an array, and parse each individually
                        preg_match_all("~" . $regex . "~iUs", $source, $matches, PREG_SET_ORDER);
                        foreach($matches as $match)
                        {
                            // process the array
                            $array = $this->parse_array($match[1], $value);

                            // Parse pair: Source, Match to be replaced, With what are we replacing?
                            $replacement = $this->parse_pair($match[2], $array);
                            
                            // Check for a parser false
                            if($replacement === "_PARSER_FALSE_") continue;
                            
                            // Check for a false reading
                            $source = str_replace($match[0], $replacement, $source);
                            $replaced_something = TRUE;
                        }
                    }

                    // Lastley, we check just plain arrays. We do this by looking for: {key.*} 
                    // .. if one exists we preg_match the array
                    if(strpos($source, $this->l_delim . $key) !== FALSE)
                    {
                        // Create our regex
                        $regex = $this->l_delim . $key . "(.*)".$this->r_delim;
                        
                        // Match all of our arrays into an array, and parse each individually
                        preg_match_all("~" . $regex . "~iUs", $source, $matches, PREG_SET_ORDER);
                        foreach($matches as $match)
                        {
                            // process the array
                            $replacement = $this->parse_array($match[1], $value);
                            
                            // If we got a false array parse, then skip the rest of this loop
                            if($replacement === "_PARSER_FALSE_") continue;
                            
                            // If our replacement is a array, it will cause an error, so just return "array"
                            if(is_array($replacement)) $replacement = "array";
                            
                            // Main replacement
                            $source = str_replace($match[0], $replacement, $source);
                            
                            // If we are putting the match back to an array key, we will cause an endless loop
                            if($replacement != $match[0]) $replaced_something = TRUE;
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
                $match = $this->l_delim . $key . $this->r_delim;
                if(strpos($source, $match) !== FALSE)
                {
                    $source = str_replace($match, $value, $source);
                    $replaced_something = TRUE;
                }
            }
            
            // Raise the counter
            ++$count;
        }
        
        // Return the parsed source
        return $source;
    }

/*
| ---------------------------------------------------------------
| Function: parse_array()
| ---------------------------------------------------------------
|
| Parses an array such as {user.userinfo.username}
|
| @Param: $key - The full unparsed array ( { something.else} )
| @Param: $array - The actual array that holds the value of $key
|
*/
    public function parse_array($key, $array)
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
            return eval('if(array_key_exists($args[$last], $array'. $s_key .')) return $array'. $s_key .'[$args[$last]]; return "_PARSER_FALSE_";');
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
        return "_PARSER_FALSE_";
    }

/*
| ---------------------------------------------------------------
| Function: parse_pair()
| ---------------------------------------------------------------
|
| Parses array blocks (  {key} ... {/key} ), sort of acts like 
| a foreach loop
|
| @Param: $match - The preg_match of the block {key} (what we need) {/key}
| @Param: $val - The array that contains the variables inside the blocks
|
*/
    public function parse_pair($match, $val)
    {	
        // Init the emtpy main block replacment
        $final_out = '';
        
        // Make sure we are dealing with an array!
        if(!is_array($val) || !is_string($match)) return "_PARSER_FALSE_";
        
        // Remove nested vars, nested vars are for outside vars
        if(strpos($match, $this->l_delim . $this->l_delim) !== FALSE)
        {
            $match = str_replace($this->l_delim . $this->l_delim, "<<!", $match);
            $match = str_replace($this->r_delim . $this->r_delim, "!>>", $match);
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
                $block = $this->parse($match, $value);
            }
            else
            {
                // Just replace {value}, as we are dealing with a string
                $block = str_replace('{value}', $value, $match);
            }
            
            // Setup a few variables to tell what loop number we are on
            if(strpos($block, "{loop.") !== FALSE)
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
        if(strpos($final_out, "<<!") !== FALSE)
        {
            $final_out = str_replace("<<!", $this->l_delim, $final_out);
            $final_out = str_replace("!>>", $this->r_delim, $final_out);
        }
        return $final_out;
    }
}
// EOF