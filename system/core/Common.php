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
	
/*
| ---------------------------------------------------------------
| Function: isValidIp()
| ---------------------------------------------------------------
|
| Returns if the given IP address is a valid, Non-Private IP
|
| @Return (Bool)
|
*/
    function isValidIp($ip)
    {
        // Trim the ip address
        $ip = trim($ip);
        if(!empty($ip) && ip2long($ip) != -1) 
        {
            $reserved_ips = array(
                array('0.0.0.0','2.255.255.255'),
                array('10.0.0.0','10.255.255.255'),
                array('127.0.0.0','127.255.255.255'),
                array('169.254.0.0','169.254.255.255'),
                array('172.16.0.0','172.31.255.255'),
                array('192.0.2.0','192.0.2.255'),
                array('192.168.0.0','192.168.255.255'),
                array('255.255.255.0','255.255.255.255')
            );

            foreach($reserved_ips as $r) 
            {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);
                if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
            }
            return true;
        }
        return false;
    }
    
    function br($count)
    {
        $buffer = '';
        for($i = 0; $i < $count; $i++)
            $buffer .= "<br />";
            
        return $buffer;
    }
// EOF