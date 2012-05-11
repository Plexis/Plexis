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
| Class: Vote_Model()
| ---------------------------------------------------------------
|
| Model for the Account::vote / Admin controller
|
*/
class Vote_Model extends Application\Core\Model 
{
    // IP address of our voter
    protected $ip = NULL;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct()
    {
        parent::__construct();
    }


/*
| ---------------------------------------------------------------
| Method: get_vote_site()
| ---------------------------------------------------------------
|
| Gets a single news post from the DB and returns the row
|
| @Param: (Int) $id - The News post ID in the DB
| @Return (Array) - An array of the vote site cloumns
|   );
|
*/
    public function get_vote_site($id)
    {
        // Get our news posts out of the database
        $query = "SELECT * FROM `pcms_vote_sites` WHERE `id`=".$id;
        $post = $this->DB->query( $query )->fetch_row();
        
        // If we have no results, return an empty array
        if($post == FALSE)
        {
            return FALSE;
        }
        
        // Return Our Data
        return $post;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_all_votesites()
| ---------------------------------------------------------------
|
| Gets all the votesites into an array, and returns it
|
| @Return (Array) - An array of the all the vote sites
|
*/
    public function get_vote_sites()
    {
        // Get our news posts out of the database
        $query = "SELECT * FROM `pcms_vote_sites`";
        $post = $this->DB->query( $query )->fetch_array();
        
        // If we have no results, return an empty array
        if($post == FALSE)
        {
            return array();
        }
        
        // Return Our Data
        return $post;
    }
    
/*
| ---------------------------------------------------------------
| Method: create()
| ---------------------------------------------------------------
|
| Adds a new vote site to the database
|
| @Param: (String) $hostname - The general hostname of the votesite
| @Param: (String) $votelink - The Direct link to vote for this server
| @Param: (String) $image_url - The Vote site logo url
| @Param: (Int) $points - Point reward for voting
| @Param: (Int) $reset_time - The reset time of the vote link
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function create($hostname, $votelink, $image_url, $points, $reset_time)
    {
        // Build out insert data
        $data['hostname'] = $hostname;
        $data['votelink'] = $votelink;
        $data['image_url'] = $image_url;
        $data['points'] = $points;
        $data['reset_time'] = $reset_time;
        
        // Insert our post
        return $this->DB->insert('pcms_vote_sites', $data);
    }
    
/*
| ---------------------------------------------------------------
| Method: update()
| ---------------------------------------------------------------
|
| Updates a vote site to the database
|
| @Param: (Int) $id - Vote Site ID
| @Param: (String) $hostname - The general hostname of the votesite
| @Param: (String) $votelink - The Direct link to vote for this server
| @Param: (String) $image_url - The Vote site logo url
| @Param: (Int) $points - Point reward for voting
| @Param: (Int) $reset_time - The reset time of the vote link
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function update($id, $hostname, $votelink, $image_url, $points, $reset_time)
    {
        // Build out insert data
        $data['hostname'] = $hostname;
        $data['votelink'] = $votelink;
        $data['image_url'] = $image_url;
        $data['points'] = $points;
        $data['reset_time'] = $reset_time;
        
        // Insert our post
        return $this->DB->update('pcms_vote_sites', $data, "`id`=$id");
    }
    
/*
| ---------------------------------------------------------------
| Method: delete()
| ---------------------------------------------------------------
|
| Deletes a news post in the database
|
| @Param: (Int) $id - Vote Site ID
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function delete($id)
    {
        // Delete our post and return the result
        return $this->DB->delete('pcms_vote_sites', "`id`=$id");
    }
    
/*
| ---------------------------------------------------------------
| Method: submit()
| ---------------------------------------------------------------
|
| Submits a vote and processes the updating of user fields
|
| @Param: (Int) $id - The account ID submitting the vote
| @Param: (Int) $site_id - Vote Site ID
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function submit($id, $site_id)
    {
        // If we are still false, then just go to inserting the new data
        $data = $this->get_data($id);
        $reset = $data[ $site_id ];
        
        // Get our current vote site
        $query = "SELECT `points`, `reset_time` FROM `pcms_vote_sites` WHERE `id`=?";
        $site = $this->DB->query( $query, array($site_id) )->fetch_row();
        
        // If our $timer is false, vote site doesnt exist
        if($site == FALSE) return FALSE;
        
        // Time check, return FALSE if there is time left before reset
        if( time() < $reset ) return FALSE;
        
        // If we are still kickin, then we are good to give the user his reward
        $data[ $site_id ] = time() + $site['reset_time'];
        $data = serialize($data);
        $update = $this->DB->update('pcms_vote_data', array('data' => $data), "`ip_address`='".$this->ip."'");
        
        // Return FALSE if the update was false
        if($update === FALSE)
        {
            return FALSE;
        }
        
        // Return the result of web points givin IF enabled
        if(config('web_points_enabled') == TRUE)
        {
            $query = "UPDATE `pcms_accounts` SET 
                `vote_points` = (`vote_points` + ".$site['points']."), 
                `votes` = (`votes` + 1),
                `vote_points_earned` = (`vote_points_earned` + ".$site['points'].")
            WHERE `id`=".$id;
            return $this->DB->query( $query );
        }
        
        // Return TRUE if we made it this far
        return TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_data()
| ---------------------------------------------------------------
|
| Processes the current vote data for a User
|
| @Param: (Int) $id - The account ID
| @Return (Array) The array of data
|
*/
    public function get_data($id)
    {
        // Get the Users IP addy
        $ip = $this->ip = load_class('Input')->ip_address();
        
        // Find the IP's donation status
        $query = "SELECT `data` FROM `pcms_vote_data` WHERE `ip_address`=?";
        $data = $this->DB->query( $query, array($ip) )->fetch_column();
        
        // If we have a false result using the IP address, then try the account ID
        if($data === FALSE)
        {
            $query2 = "SELECT `data` FROM `pcms_vote_data` WHERE `account_id`=?";
            $data = $this->DB->query( $query, array($id) )->fetch_column();
            
            // If this result is false as well, then just create new data
            if($data === FALSE)
            {
                // Build our insert data
                $insert = array(
                    'account_id' => $id,
                    'ip_address' => $ip,
                    'data' => NULL
                );
                $this->DB->insert('pcms_vote_data', $insert);
            }
            else
            {
                // Update our account ID with the new Ip 
                $this->DB->update('pcms_vote_data', array('ip_address' => $ip), "`account_id`=".$id);
                $data = unserialize($data);
            }
        }
        else
        {
            $data = unserialize($data);
        }

        // Get alist of installed vote sites!
        $query = "SELECT `id` FROM `pcms_vote_sites`";
        $result = $this->DB->query( $query )->fetch_array();
        $list = array();
        foreach($result as $temp)
        {
            $list[] = $temp['id'];
        }
        
        // If we dont have data, init an empty one
        if(!is_array($data))
        {
            $data = array();
            goto Add;
        }
        
        // Remove all Old vote sites that are no longer installed
        foreach($data as $key => $value)
        {
            // Make sure the site still exists
            $k = array_search($key, $list);
            if($k !== FALSE)
            {
                // remove this site from the "list" becuase its still installed
               unset($list[$k]); 
            }
            else
            {
                // Remove this vote site from the users data because it doesnt exist anymore
                unset($data[$key]);
            }
        }
        
        Add:
        {
            // Now we need to add whatever is left in the list of installed sites
            if(count($list) > 0)
            {
                foreach($list as $site)
                {
                    // Add the site and set the reset time to now
                    $data[ $site['id'] ] =  time();
                }
            }
        }

        // Return the users vote data
        return $data;
    }
}
// EOF