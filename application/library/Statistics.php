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
        $url = $this->Router->get_url_info();
        $page = $url['uri'] .'/';

        // Only add hit if the IP is valid
        if( $Ip !== false && $Ip !== -1 )
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
            $query = "INSERT INTO pcms_hits(ip, page_url, hits) VALUES('$Ip', '$page', 1) ON DUPLICATE KEY UPDATE `hits` = (`hits` +1)";
            $this->DB->query( $query )->num_rows();           
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: get_popular_pages()
| ---------------------------------------------------------------
|
| This method returns the total hits, and unique hits for all
| previously viewed pages
|
| @Param: $limit - Number of max results to return
|
*/
    public function get_popular_pages($limit = 100)
    {
        // First, get a list of distinct page names
        $query = "SELECT DISTINCT `page_url` FROM `pcms_hits` ORDER BY `hits` DESC LIMIT $limit";
        $results = $this->DB->query( $query )->fetch_array();
        
        // No pages in the database :O
        if(!$results) return array();
        
        // build our return array and loop through each page
        $return = array();
        foreach($results as $page)
        {
            // Get the total page views and unique hits
            $page = $page['page_url'];
            $query = "SELECT SUM(hits) AS `total`, COUNT(ip) AS `unique` FROM `pcms_hits` WHERE `page_url` = '$page'";
            $array = $this->DB->query( $query )->fetch_row();
            $return[] = array('page' => $page, 'total' => (int)$array['total'], 'unique' => (int)$array['unique']);
        }
        
        // Return the results :)
        return $return;
    }
    
/*
| ---------------------------------------------------------------
| Function: get_stats_by_page()
| ---------------------------------------------------------------
|
| This method returns the total hits and unique hits by page URI.
|
| @Param: $page - the URI (controller/action/qs)
|
*/
    public function get_stats_by_page($page)
    {
        $query = "SELECT SUM(hits) AS `total`, COUNT(ip) AS `unique` FROM `pcms_hits` WHERE `page_url` = '$page'";
        $array = $this->DB->query( $query )->fetch_row();
        
        // Return the results :)
        return $array;
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