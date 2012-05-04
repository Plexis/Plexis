<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Statistics()
| ---------------------------------------------------------------
|
| The cms' website stats class
|
*/
namespace Application\Library;

class Statistics
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/ 
    public function __construct()
    {
        $this->load = load_class('Loader');
        $this->DB = $this->load->database('DB');
        $this->Router = load_class('Router');
        $this->Input = load_class('Input');
    }
    
/*
| ---------------------------------------------------------------
| Function: add_hit()
| ---------------------------------------------------------------
|
| This function adds a "hit" to our database.
|
| @Param: $page - the URI (controller/action/qs)
|
*/
    public function add_hit()
    {
        // Get IP address and URL info
        $Ip = ip2long( $this->get_ip() );

        // Only add hit if the IP is valid
        if( $Ip != false && $Ip != -1 )
        {
            $Ip = sprintf("%u", $Ip);
            
            // Now check the cookie incase the users IP address changes
            $cookie = $this->Input->cookie('visiter_id', true);

            // Ip changed checking
            if($Ip != $cookie)
            {
                // Set a new cookie and update current records, expire time 3 years :)
                $this->Input->set_cookie('visitor_id', $Ip, (time() + 94608000));
                $query = "UPDATE `pcms_hits` SET `ip` = '$Ip' WHERE `ip` = '$cookie'";
                $this->DB->query( $query );
            }
        
            // Update hit count
            $query = "INSERT INTO pcms_hits(ip, hits) VALUES('$Ip', 1) ON DUPLICATE KEY UPDATE `hits` = (`hits` +1)";
            $this->DB->query( $query )->num_rows();           
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: get_ip()
| ---------------------------------------------------------------
|
| This function gets the remote hosts ip address
|
*/
    public function get_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
          return $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            if(is_array($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                return $_SERVER['HTTP_X_FORWARDED_FOR'][0];
            }
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
          return $_SERVER['REMOTE_ADDR'];
        }
    }
}