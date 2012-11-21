<?php
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
        // Get our path parts
        $parts = func_get_args();
        return (is_array( $parts )) ? trim( implode(DS, $parts) ) : trim($parts);
    }