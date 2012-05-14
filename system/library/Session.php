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
*/
namespace System\Library;

class Session
{
    // Have we already started the session?
    protected $started = FALSE;

    // Array of session data
    public $data = array();

    // Database / cookie info
    protected $session_use_db;
    protected $session_db_id;
    protected $session_table_name;
    protected $session_cookie_name;
    protected $db_session_exists;

    // Our DB connection
    protected $DB;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/
    public function __construct()
    {		
        // start the session
        $this->start_session();
        
        // Get our DB information
        $this->session_use_db = config('session_use_database', 'Core');
        $this->session_db_id = config('session_database_id', 'Core');
        $this->session_table_name = config('session_table_name', 'Core');
        $this->session_cookie_name = config('session_cookie_name', 'Core');
        
        // Init the loader class
        $this->load = load_class('Loader');
        
        // load the Input class
        $this->input = load_class('Input');
        
        // Are we storing session data in the database? If so, Load the DB connction
        if($this->session_use_db == TRUE)
        {
            $this->DB = $this->load->database( $this->session_db_id );
            $this->db_session_exists = FALSE; // default
        }
        else
        {
            // load the Cache class for session file writting
            $this->cache = $this->load->library('Cache');
        }
        
        // Check for session data. If there is none, create it.
        if(!$this->check())
        {
            $this->create();
        }
    }

/*
| ---------------------------------------------------------------
| Method: start_session()
| ---------------------------------------------------------------
|
| Only starts a session if its not already set
|
*/
    protected function start_session()
    {
        if(!$this->started)
        {
            session_start();
            $this->started = TRUE;
        }
    }

/*
| ---------------------------------------------------------------
| Method: create()
| ---------------------------------------------------------------
|
| Creates session data.
|
*/
    protected function create()
    {
        // Generate a completely random session id
        $time = microtime(1);
        $string = sha1(base64_encode(md5(utf8_encode( $time ))));
        $this->data['token'] = substr($string, 0, 20);
    }

/*
| ---------------------------------------------------------------
| Method: check()
| ---------------------------------------------------------------
|
| Read session data, and cookies to determine if our session
| is still alive. We also match the Users IP / User agent
| info to prevent cookie thiefs getting in as an unauth'd user.
|
*/
    protected function check()
    {
        // Check for a session cookie
        $cookie = $this->input->cookie( $this->session_cookie_name );
        
        // If the cookie doesnt exists, then neither does the session
        if($cookie == FALSE)
        {
            return FALSE;
        }
        
        // Read cookie data to get our token
        $token = base64_decode( $cookie );
        
        // Are we storing session data in the database?
        if($this->session_use_db == TRUE)
        {				
            // Get the database result
            $query = "SELECT * FROM `". $this->session_table_name ."` WHERE `token` = ?";
            $result = $this->DB->query( $query, array($token) )->fetch_row();
            
            // Unserialize the user_data array
            if($result !== FALSE)
            {
                $this->db_session_exists = TRUE;
                $result['user_data'] = unserialize( $result['user_data'] );
            }
        }
        
        // config says, No sessions in database, Load from cache
        else
        {
            // check users IP address to prevent cookie stealing
            $result = $this->cache->get('session_'. $token);		
        }
        
        // If we have a result, then data IS in the DB
        if($result !== FALSE)
        {
            // Set our token
            $this->data['token'] = $result['token'];
            
            // check users IP address to prevent cookie stealing
            if( $result['ip_address'] == $this->input->ip_address() )
            {
                // Set data if we have any
                if(count($result['user_data']) > 0)
                {
                    foreach($result['user_data'] as $key => $value)
                    {
                        // Set the data in the session
                        $this->set($key, $value);
                    }
                }
            
                // Update last seen
                $this->data['last_seen'] = time();
            
                // Return success
                return TRUE;
            }
            else
            {
                // Ip address changed, destroy the current session
                $this->destroy();
            }
        }
        
        // The result was false, return FALSE
        return FALSE;
    }

/*
| ---------------------------------------------------------------
| Method: save()
| ---------------------------------------------------------------
|
| Saves the current session token in a cookie, and in the DB for
| things like "Remeber Me" etc etc.
|
*/
    public function save()
    {
        // Set a cookie with the session token
        $cookie_data = base64_encode( $this->data['token'] );

        // Set the cookie
        $this->input->set_cookie( $this->session_cookie_name, $cookie_data );
        
        // Create user data array
        $data = array(
            'token' => $this->data['token'],  
            'ip_address' => $this->input->ip_address(),
            'last_seen' => time(),
        );

        // If we are stroing session data in the DB, lets do that
        if($this->session_use_db == TRUE)
        {	
            // Add session data to the DB only if it doesnt exists
            if ($this->db_session_exists == FALSE)
            {	
                // Add user data
                $data['user_data'] = serialize( $this->data );
            
                // Insert
                $this->DB->insert( $this->session_table_name, $data );
            }
            
            // Session data does exists for this token, so update.
            else
            {
                return $this->update();
            }
            
            // Return the the result
            if($this->DB->num_rows() > 0)
            {
                return TRUE;
            }
            return FALSE;
        }
        
        // If we arent storing session Data in the DB, then we save to cache
        else
        {
            // Add user data
            $data['user_data'] = $this->data;
            
            // Users can manage thier own expire time... 1 year for here.
            $expire = (60 * 60 * 24 * 365);
            return $this->cache->save('session_'.$data['token'], $data, $expire);
        }
    }


/*
| ---------------------------------------------------------------
| Method: destroy()
| ---------------------------------------------------------------
|
| Ends the current Session|
|
*/
    public function destroy()
    {	
        // Expire the cookie
        $ID = base64_encode( $this->data['token'] );
        $time = time() - 1;
        $this->input->set_cookie( $this->session_cookie_name, $ID,  $time);

        // Are we storing session data in the database? 
        // If so then remove the session from the DB, else remove cache data
        if($this->session_use_db == TRUE)
        {			
            $this->DB->delete( $this->session_table_name, "`token` = '". $this->data['token'] ."'");
        }
        else
        {
            $this->cache->delete( 'session_'.$this->data['token'] );
        }
        
        // Remove session variables and cookeis
        $this->data = array();
        $this->started = FALSE;
        session_destroy();
        
        // Start a new session
        $this->start_session();
        $this->create();
    }

/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Returns a session variable
|
| @Param: (String) $name - variable name to be returned
|
*/
    public function get($name)
    {
        if(isset($this->data[$name]))
        {
            return $this->data[$name]; 
        }
        
        // Didnt exist, return NULL
        return NULL;
    }

/*
| ---------------------------------------------------------------
| Method: set()
| ---------------------------------------------------------------
|
| Sets a session variable.
|
| @Param: (String) $name - variable name to be set, OR an array 
|   of $names => $values
| @Param: (Mixed) $value - value of the variable, or NULL if $name 
|   is array.
|
*/
    public function set($name, $value = NULL)
    {	
        // Check for an array
        if(is_array($name))
        {
            foreach($name as $key => $value)
            {
                $this->data[$key] = $value;
            }
        }
        else
        {
            $this->data[$name] = $value;
        }
    }

/*
| ---------------------------------------------------------------
| Method: delete()
| ---------------------------------------------------------------
|
| Unsets a session variable
|
*/
    public function delete($name)
    {
        unset($this->data[$name]);
    }

/*
| ---------------------------------------------------------------
| Method: update()
| ---------------------------------------------------------------
|
| Updates the the session data thats in the database
|
*/
    protected function update()
    {
        // Are we storing session data in the database? 
        // If so then update the session last_seen
        if($this->session_use_db == TRUE)
        {
            // Update data
            $ID = serialize( $this->data );
            
            // Prep data
            $table = $this->session_table_name;
            $data = array( 'last_seen' => time(), 'user_data' => $ID );
            $where = "`token` = '". $this->data['token'] ."'";

            // return the straight db result
            return $this->DB->update( $table, $data, $where );
        }
    }
}
// EOF