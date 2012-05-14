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
        $Ip = ip2long( $this->Input->ip_address() );
        $Time = time();

        // Only add hit if the IP is valid
        if( $Ip != false && $Ip != -1 )
        {
            // Make the IP valid to use with MySQL's INET_NTOA() functions
            $Ip = sprintf("%u", $Ip);
            
            // Now check the cookie incase the users IP address changes
            $cookie = $this->Input->cookie('visitor_id', true);
            
            // No cookie?
            if($cookie == false)
            {
                // Set a cookie
                $this->Input->set_cookie('visitor_id', $Ip ."_". $Time, ($Time + 94608000));
                $query = "INSERT INTO pcms_hits(ip, lastseen) VALUES('$Ip', '". $Time ."') ON DUPLICATE KEY UPDATE `lastseen` = ". $Time;
                $this->DB->query( $query );  
            }
            else
            {
                // Make sure the cookie is valid!
                if(strpos($cookie, '_') === false)
                {
                    $this->Input->set_cookie('visitor_id', $Ip ."_". $Time, ($Time + 94608000));
                }
                else
                {
                    // Explode the cookie
                    list($i, $t) = explode('_', $cookie);
                    
                    // If the IP doesnt match, or the cookie was set over 15 minutes ago
                    if($Ip != $i || ($Time - 900) > $t)
                    {
                        // Set a new cookie and update current records, expire time 3 years :)
                        $this->Input->set_cookie('visitor_id', $Ip ."_". $Time, ($Time + 94608000));
                        $query = "UPDATE `pcms_hits` SET `ip` = '$Ip', `lastseen` = '".$Time ."' WHERE `ip` = ?";
                        $result = $this->DB->query( $query, array($i) )->num_rows();
                        
                        // If the update failed, just insert new record
                        if($result == false)
                        {
                            $query = "INSERT INTO pcms_hits(ip, lastseen) VALUES('$Ip', '". $Time ."') ON DUPLICATE KEY UPDATE `lastseen` = ". $Time;
                            $this->DB->query( $query );
                        }
                    }
                }
            }       
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: get_hits()
| ---------------------------------------------------------------
|
| This method returns the total hits and unique hits.
|
*/
    public function get_hits()
    {
        // Unique views
        $query = "SELECT COUNT(ip) FROM `pcms_hits`";
        $unique = $this->DB->query( $query )->fetch_column();
        
        // Vists in the last 24 hours
        $query = "SELECT COUNT(ip) FROM `pcms_hits` WHERE `lastseen` > ". (time() - 86400);
        $vists = $this->DB->query( $query )->fetch_column();
        
        // Return the results :)
        return array('unique' => $unique, 'today' => $vists);
    }
}