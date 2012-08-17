<?php
/*
| ---------------------------------------------------------------
| Function: typeToString()
| ---------------------------------------------------------------
|
| Converts a select ID on the variable type to a string
|
| @Param: (Int)
| @Return: (String) - The path, with the corrected Directory Seperator
|
*/
    function typeToString($type)
    {
        switch($type)
        {
            case 0: return 'string';
            case 1: return 'int';
            case 2: return 'bool';
            case 3: return 'float';
            case 4: return 'double';
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: output()
| ---------------------------------------------------------------
|
| Outputs data that the JS script can read, and kills the script
|
*/
    function output($data)
    {
        echo json_encode($data);
        die();
    }
    
/*
| ---------------------------------------------------------------
| Function: getFileContents()
| ---------------------------------------------------------------
|
| Gets the file contents from the plexis directory, and adds
| line numbers, and coloring.
|
*/
    function getFileContents($file, $line)
    {
        // Open the file into an array of each line
        $file = realpath(PLEXIS_PATH . DS . $file);
        $file_lines = file_get_contents($file);
        $file_lines = explode('<br />', htmlspecialchars_decode( highlight_string($file_lines, true)));
        
        // File size variables, and output array
        $total_lines = count($file_lines);
        $total_line_length = strlen($total_lines);
        $output = array();
        
        for($i=0; $i < $total_lines; $i++) 
        {
            $cline = str_pad(($i + 1), $total_line_length, "0", STR_PAD_LEFT);
            $current_line = (($i + 1) == $line) ? '<b class="l"><font color="red">'. $cline .':</font></b> ' : '<b class="l"><font color="black">'. $cline .':</font></b> ';
            $line_data = preg_replace_callback('/\$[a-zA-Z0-9_\[\]\'\"]+/', 'highlightVars', $file_lines[$i]);
            // $line_data = preg_replace_callback('/\$[A-Za-z_]{1}[A-Za-z0-9_]*\s*\[\s*(\'|"|).*?(\'|"|)\s*\]/i', 'highlightVars', $file_lines[$i]);
            $output[] = $current_line . $line_data;
        }
        return implode('<br />', $output);
    }
    
/*
| ---------------------------------------------------------------
| Function: highlightVars()
| ---------------------------------------------------------------
|
| Converts all variables to a clickable link
|
*/
    function highlightVars($matches)
    {
        $find = array('"', "'");
        $class = 'class="r"';
        return '<a href="#" '.$class.' onclick="getVar(\''.str_replace($find, '', $matches[0]).'\', 1); return false;">'.$matches[0].'</a>';
    }
    
/*
| ---------------------------------------------------------------
| Function: highlight()
| ---------------------------------------------------------------
|
| Highlights variables for the console window
|
*/
    function highlight($string)
    {
        // Format will be like so $var = somevalue
        $parts = explode(' = ', $string);
        
        $find = array('"', "'", '[]', ']');
        $v_keys = explode('[', str_replace($find, '', $parts[0]));
        $num_of_keys = count($v_keys);
        $str = "<span class='c_keyword'>{$v_keys[0]}</span>";
        
        for($i = 1; $i < $num_of_keys; $i++)
        {
            $str .= "<span class='c_keyword'>[</span><span class='c_arraykey'>{$v_keys[$i]}</span><span class='c_keyword'>]</span>";
        }
        
        $parts[0] = $str;
        
        // For variable errors
        if(isset($parts[1]) && $parts[1] == 'Undefined Variable')
        {
            $parts[1] = "<span class='c_error'>{$parts[1]}</span>";
        }
        
        return implode(' = ', $parts);
    }
    
/*
| ---------------------------------------------------------------
| Function: path()
| ---------------------------------------------------------------
|
| Combines several strings into a file path.
|
| @Params: (String | Array) - The pieces of the path, passed as 
|   individual arguments. Each argument can be an array of paths,
|   a string foldername, or a mixture of the two.
| @Return: (String) - The path, with the corrected Directory Seperator
|
*/

    function path()
    {
        // Determine if we are one windows, And get our path parts
        $IsWindows = strtoupper( substr(PHP_OS, 0, 3) ) === "WIN";
        $args = func_get_args();
        $parts = array();
        
        // Trim our paths to remvove spaces and new lines
        foreach( $args as $part )
        {
            $parts[] = (is_array( $part )) ? trim( implode(DS, $part) ) : trim($part);
        }

        // Get our cleaned path into a variable with the correct directory seperator
        $newPath = implode( DS, $parts );
        
        // Do some checking for illegal path chars
        if( $IsWindows )
        {
            $IllegalChars = "\\/:?*\"<>|\r\n";
            $Pattern = "~[" . $IllegalChars . "]+~";
            $tempPath = preg_replace( "~^[A-Z]{1}:~", "", $newPath );
            $tempPath = trim( $tempPath, DS );
            $tempPath = explode( DS, $tempPath );
            
            foreach( $tempPath as $part )
            {
                if( preg_match( $Pattern, $part ) )
                {
                    show_error( "illegal_chars_in_path", array( $part ) );
                    return null;
                }
            }
        }
        
        return $newPath;
    }