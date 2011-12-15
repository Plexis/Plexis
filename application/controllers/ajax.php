<?php
class Ajax extends Application\Core\Controller 
{
    // Our user
    protected $user;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/    
    public function __construct()
    {
        // Build the Core Controller
        parent::__construct();
        
        // Init a session var
        $this->user = $this->Session->get('user');
    }
    
/*
| ---------------------------------------------------------------
| Method: check_access()
| ---------------------------------------------------------------
|
| This method is used for certain Ajax pages to see if the requester
|   has permission to recieve the request.
|
| @Param: $lvl - The account level required (a = admin, u = user)
*/   
    protected function check_access($lvl = 'a')
    {
        // Make sure the user has admin access'
        if($lvl == 'a' && ($this->user['is_admin'] != 1 || $this->user['is_super_admin'] != 1))
        {
            echo "403";
            die();
        }
        elseif($lvl == 'u' && !$this->user['is_loggedin'])
        {
            echo "403";
            die();
        }
    }

/*
| ---------------------------------------------------------------
| Method: users()
| ---------------------------------------------------------------
|
| This method is used for an Ajax request to list users
*/    
    public function users()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_access('a');
        
        // Load the Ajax Model
        $this->load->model("Ajax_Model", "ajax");
        
        /* 
        * Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $cols = array( 'id', 'username', 'email', 'group_id' );
        
        /* Indexed column (used for fast and accurate table cardinality) */
        $index = "id";
        
        /* DB table to use */
        $table = "pcms_accounts";
        
        /* Database to use */
        $dB = "DB";
        
        /* Process the request */
        $output = $this->ajax->process_datatables($cols, $index, $table, $dB);
        
        // Get our user groups
        $DB = $this->load->database('DB');
        $Groups = $DB->query("SELECT `group_id`,`title` FROM `pcms_account_groups`")->fetch_array();
        foreach($Groups as $value)
        {
            $groups[ $value['group_id'] ] = $value['title'];
        }
        
        // We need to add a working "manage" link
        foreach($output['aaData'] as $key => $value)
        {
            $output['aaData'][$key][3] = $groups[ $output['aaData'][$key][3] ];
            $output['aaData'][$key][4] = "<a href=\"". SITE_URL ."/admin/users/".$value[1]."\">Manage</a>";
        }
        
        // Push the output in json format
        echo json_encode($output);
    }
    
/*
| ---------------------------------------------------------------
| Method: users()
| ---------------------------------------------------------------
|
| This method is used for an Ajax request to list users
*/    
    public function account($username)
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_access('a');
        
        // If we recieved POST actions, then process it as ajax
        if(isset($_POST['action']))
        {
            // Load the database
            $this->load->database( 'DB' );
            
            // Get users information. We can use GET because the queries second param will be cleaned
            // by the PDO class when bound to the "?".
            $query = "SELECT * FROM `pcms_accounts` WHERE `username` LIKE ?";
            $user = $this->DB->query( $query, array($username) )->fetch_row();
            $id = $user['id'];
   
            // Make sure the current user has privlages to execute an ajax
            if( (!$this->user['is_super_admin'] && $user['is_admin']) && $_POST['action'] !== 'account-status' )
            {
                $this->output(false, 'access_denied_privlages');
                return;
            }
  
           // Get our post action
            switch($_POST['action'])
            {
                case "ban-account":
                    // Set our variables
                    $date = strtotime($_POST['unbandate']);
                    $reason = $_POST['banreason'];
                    $banip = (isset($_POST['banip'])) ? $_POST['banip'] : FALSE;
                    
                    // Process the Ban, and process the return result
                    $result = $this->realm->ban_account($id, $reason, $date, $this->user['username'], $banip);
                    ($result == TRUE) ? $this->output(true, 'account_ban_success') : $this->output(false, 'account_ban_error');
                    break;
                    
                case "unban-account":
                    $result = $this->realm->unban_account($id);
                    ($result == TRUE) ? $this->output(true, 'account_unban_success') : $this->output(false, 'account_unban_error');
                    break;
                    
                case "lock-account":
                    $result = $this->realm->lock_account($id);
                    ($result == TRUE) ? $this->output(true, 'account_lock_success') : $this->output(false, 'account_lock_error');
                    break;
                    
                case "unlock-account":
                    $result = $this->realm->unlock_account($id);
                    ($result == TRUE) ?$this->output(true, 'account_unlock_success') : $this->output(false, 'account_unlock_error');
                    break;
                    
                case "delete-account":
                    if( $this->realm->delete_account($id) )
                    {
                        // Load the database, Delete user
                        $this->DB = $this->load->database('DB');
                        $result = $this->DB->delete("pcms_accounts", "`id`=".$id);
                        ($result == TRUE) ? $this->output(true, 'account_delete_success') : $this->output(false, 'account_delete_error');
                    }
                    else
                    {
                        $this->output(false, 'account_delete_error');
                    }
                    break;
                    
                case "account-status":
                    // Use the realm database to grab user information first
                    $profile = $this->realm->fetch_account($id);
                    if($profile !== FALSE)
                    {
                        $status = $this->realm->account_banned($profile['id']);
                        if($status == FALSE)
                        {
                            ($profile['locked'] == FALSE) ? $status = 'Active' : $status = 'Locked';
                        }
                        else
                        {
                            $status = 'Banned';
                        };
                    }
                    else
                    {
                        $status = 'Unknown';
                    }
                    $this->output(true, $status);
                    break;
                    
                case "update-account":
                    $this->update_account($id, $user);
                    break;  
            }
            return;
        }
    }

/*
| ---------------------------------------------------------------
| Method: news()
| ---------------------------------------------------------------
|
| This method is used to list news post via an Ajax request
*/    
    public function news()
    {
        // Check for 'action' posts
        if(isset($_POST['action']))
        {
            // Make sure we arent getting direct accessed, and the user has permission
            $this->check_access('a');
        
            // Load the News Model
            $this->load->model('News_Model');
        
            // Get our action type
            switch($_POST['action']) 
            {
                // CREATING
                case "create":
                    // Load the Form Validation script
                    $this->load->library('validation');
                    
                    // Tell the validator that the username and password must NOT be empty
                    $this->validation->set( array(
                        'title' => 'required', 
                        'body' => 'required')
                    );
                    
                    // If both the username and password pass validation
                    if( $this->validation->validate() == TRUE )
                    {
                        $result = $this->News_Model->submit_news($_POST['title'], $_POST['body'], $this->user['username']);
                        ($result == TRUE) ? $this->output(true, 'news_posted_successfully') : $this->output(false, 'news_post_error');
                    }
                    
                    // Validation failed
                    else
                    {
                        $this->output(false, 'news_validation_error');
                    }
                    break;
                
                // EDITING
                case "edit":
                    // Load the Form Validation script
                    $this->load->library('validation');
                    
                    // Tell the validator that the username and password must NOT be empty
                    $this->validation->set( array(
                        'title' => 'required', 
                        'body' => 'required')
                    );
                    
                    // If both the username and password pass validation
                    if( $this->validation->validate() == TRUE )
                    {
                        $result = $this->News_Model->update_news($_POST['id'], $_POST['title'], $_POST['body']);
                        ($result == TRUE) ? $this->output(true, 'news_update_success') : $this->output(false, 'news_update_error', 'warning');
                    }
                    
                    // Validation failed
                    else
                    {
                        $this->output(false, 'news_validation_error');
                    }
                    break;
                    
                // DELETING
                case "delete":
                    $result = $this->News_Model->delete_post($_POST['id']);
                    ($result == TRUE) ? $this->output(true, 'news_delete_success') : $this->output(false, 'news_delete_error');
                    break;
            }
        }
        else
        {
            // Load the Ajax Model
            $this->load->model("Ajax_Model", "ajax");
            
            /* 
            * Array of database columns which should be read and sent back to DataTables. Use a space where
            * you want to insert a non-database field (for example a counter or static image)
            */
            $cols = array( 'id', 'title', 'author', 'posted' );
            
            /* Indexed column (used for fast and accurate table cardinality) */
            $index = "id";
            
            /* DB table to use */
            $table = "pcms_news";
            
            /* Database to use */
            $dB = "DB";
            
            /* Process the request */
            $output = $this->ajax->process_datatables($cols, $index, $table, $dB);
            
            // We need to add a working "manage" link
            foreach($output['aaData'] as $key => $value)
            {
                $output['aaData'][$key][3] = date("F j, Y, g:i a", $output['aaData'][$key][3]);
                $output['aaData'][$key][4] = '<a href="'. SITE_URL .'/admin/news/edit/'.$value[0].'">Edit</a> 
                    - <a class="delete" name="'.$value[0].'" href="javascript:void(0);">Delete</a>';
            }
            
            // Push the output in json format
            echo json_encode($output);
        }
    }
 
/*
| ---------------------------------------------------------------
| Method: settings()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to save config files
*/  
    public function settings($type = 'App')
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_access('a');
        
        // Load our config class
        $Config = load_class('Config');
        
        // Do we have POST action?
        if(isset($_POST['action']) && $_POST['action'] == 'save')
        {
            foreach($_POST as $item => $val) 
            {
                $key = explode('__', $item);
                if($key[0] == 'cfg') 
                {
                    $Config->set($key[1], $val, $type);
                }
            }
            
            // Determine if our save is a success
            $result = $Config->save($type);
            ($result == TRUE) ? $this->output(true, 'config_save_success') : $this->output(false, 'config_save_error');
        }
    }

/*
| ---------------------------------------------------------------
| Method: database()
| ---------------------------------------------------------------
|
| This method is used for via Ajax for the database operations
*/      
    function database()
    {
        // Do we have POST action?
        if(isset($_POST['action']) && $_POST['action'] == 'save')
        {
            // Load our config class
            $Config = load_class('Config');
        
            // Make sure user is super admin for ajax
            if($this->user['is_super_admin'] != 1)
            {
                $this->output(false, 'access_denied_privlages');
                return;
            }
            
            // Build our new config settings
            $configs = array(
                'DB' => array(
                    'driver'	   => $_POST['cms_db_driver'],
                    'host'         => $_POST['cms_db_host'],
                    'port'         => $_POST['cms_db_port'],
                    'username'     => $_POST['cms_db_username'],
                    'password'     => $_POST['cms_db_password'],
                    'database'     => $_POST['cms_db_database']
                ),
                'RDB' => array(
                    'driver'	   => $_POST['cms_rdb_driver'],
                    'host'         => $_POST['cms_rdb_host'],
                    'port'         => $_POST['cms_rdb_port'],
                    'username'     => $_POST['cms_rdb_username'],
                    'password'     => $_POST['cms_rdb_password'],
                    'database'     => $_POST['cms_rdb_database']
                )
            );
            
            // Turn off all error reporting, since we use our own, this is easy
            $old = $Config->get_all('Core');
            $Config->set(array('environment' => 1, 'log_errors' => 0), false, 'Core');

            // Test our new connections before saving to config
            $a = $configs['DB'];
            try{
                $dba = new \pdo( 
                    $a['driver'].':host='.$a['host'].':'.$a['port'].';dbname='.$a['database'], 
                    $a['username'], 
                    $a['password'],
                    array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
                );
            }
            catch(\PDOException $e){
                $this->output(false, '<strong>Connection Error:</strong> '. $e->getMessage());
                return;
            }
            
            // Test our new connection first
            $a = $configs['RDB'];
            try{
                $dba = new \pdo( 
                    $a['driver'].':host='.$a['host'].':'.$a['port'].';dbname='.$a['database'], 
                    $a['username'], 
                    $a['password'],
                    array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
                );
            }
            catch(\PDOException $e){
                $this->output(false, '<strong>Connection Error:</strong> '. $e->getMessage());
                return;
            }
            
            // Re-enable errors
            $Config->set(array('log_errors' => $old['log_errors'], 'environment' => $old['environment']), NULL, 'Core');

            // Set our configs
            $Config->set($configs, false, 'DB');
            
            // Determine if our save is a success
            $result = $Config->save('DB');
            ($result == TRUE) ? $this->output(true, 'config_save_success') : $this->output(false, 'config_save_error');
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: realms()
| ---------------------------------------------------------------
|
| This method is used to update / install realms via Ajax
*/    
    public function realms()
    {
        // Find out what we are doing here by our requested action
        switch($_POST['action'])
        {
            case "install":
                $this->check_access('a');
                $action = "install";
                break;
                
            case "edit":
                $this->check_access('a');
                $action = "edit";
                break;
                
            case "un-install":
                $this->check_access('a');
                $action = "un-install";
                break;
                
            case "status":
                break;
                
            case "admin":
                $this->check_access('a');
                $action = "admin";
                break;
                
            case "info":
                break;
        }

        
        // Process our action
        if($action == 'admin')
        {
            // Load the Ajax Model
            $this->load->model("Ajax_Model", "ajax");
            
            /* 
            * Array of database columns which should be read and sent back to DataTables. Use a space where
            * you want to insert a non-database field (for example a counter or static image)
            */
            $cols = array( 'id', 'name', 'address', 'port' );
            
            /* Indexed column (used for fast and accurate table cardinality) */
            $index = "id";
            
            /* DB table to use */
            $table = "realmlist";
            
            /* Database to use */
            $dB = "RDB";
            
            /* Process the request */
            $output = $this->ajax->process_datatables($cols, $index, $table, $dB);
            
            
            // Get our installed realms
            $realms = $output['aaData'];
            $installed = get_installed_realms();
            $irealms = array();
            
            // Build an array of installed IDs
            foreach($installed as $realm)
            {
                $irealms[] = $realm['id'];
            }

            // We need to add a working "manage" link
            $key = 0;
            foreach($output['aaData'] as $realm)
            {
                // Easier to write
                $id = $realm[0];
                
                // Create out action links for this realm
                if(in_array($id, $irealms))
                {
                    $output['aaData'][$key][4] = "<font color='green'>Installed</font>";
                    $output['aaData'][$key][5] = "<a href=\"". SITE_URL ."/admin/realms/edit/".$id."\">Update</a> 
                        - <a class=\"un-install\" name=\"".$id."\" href=\"javascript:void(0);\">Uninstall</a>";
                }
                else
                {
                    $output['aaData'][$key][4] = "<font color='red'>Not Installed</font>";
                    $output['aaData'][$key][5] = "<a href=\"". SITE_URL ."/admin/realms/install/".$id."\">Install Realm</a>";
                }
                ++$key;
            }

            // Push the output in json format
            echo json_encode($output);
        }
        
        // Installing / Editing
        elseif($action == 'install' || $action == 'edit')
        {
            // Load our config class
            $Config = load_class('Config');
    
            // Get our posted information
            $id = $_POST['id'];
            $name = $_POST['name'];
            $address = $_POST['address'];
            $port = $_POST['port'];
            $type = $_POST['type'];
            $driver = $_POST['driver'];
            $cs = $_POST['c_driver'].";".$_POST['c_address'].";".$_POST['c_port'].";".$_POST['c_username'].";".$_POST['c_password'].";".$_POST['c_database'];
            $ws = $_POST['w_driver'].";".$_POST['w_address'].";".$_POST['w_port'].";".$_POST['w_username'].";".$_POST['w_password'].";".$_POST['w_database'];
            $ra = $_POST['ra_type'].";".$_POST['ra_port'].";".$_POST['ra_username'].";".$_POST['ra_password'];
            
            // See if we can establish a connection
            $configs = array(
                'CDB' => array(
                    'driver'	   => $_POST['c_driver'],
                    'host'         => $_POST['c_address'],
                    'port'         => $_POST['c_port'],
                    'username'     => $_POST['c_username'],
                    'password'     => $_POST['c_password'],
                    'database'     => $_POST['c_database']
                ),
                'WDB' => array(
                    'driver'	   => $_POST['w_driver'],
                    'host'         => $_POST['w_address'],
                    'port'         => $_POST['w_port'],
                    'username'     => $_POST['w_username'],
                    'password'     => $_POST['w_password'],
                    'database'     => $_POST['w_database']
                )
            );
            
            // Turn off all error reporting, since we use our own, this is easy
            $debug = load_class('Debug');
            $debug->error_reporting(FALSE);
            $good = TRUE;

            // Test our new connections before saving to config
            $a = $configs['CDB'];
            try{
                $dba = new \pdo( 
                    $a['driver'].':host='.$a['host'].':'.$a['port'].';dbname='.$a['database'], 
                    $a['username'], 
                    $a['password'],
                    array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
                );
            }
            catch(\PDOException $e){
                $good = FALSE;
            }
            
            // Test our new connection first
            $a = $configs['WDB'];
            try{
                $dba = new \pdo( 
                    $a['driver'].':host='.$a['host'].':'.$a['port'].';dbname='.$a['database'], 
                    $a['username'], 
                    $a['password'],
                    array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
                );
            }
            catch(\PDOException $e){
                $good = FALSE;
            }
            
            // Re-enable errors
            $debug->error_reporting(TRUE);
            
            // Install our new stuffs
            $data = array(
                'id' => $id,
                'name' => $name,
                'address' => $address,
                'port' => $port,
                'type' => $type,
                'char_db' => $cs,
                'world_db' => $ws,
                'ra_info' => $ra,
                'driver' => $driver
            );
            
            // Process our return message
            if($action == 'install')
            {
                $result = $this->DB->insert('pcms_realms', $data);
                if($result == FALSE)
                {
                    $this->output(false, 'realm_install_error');
                    return;
                }
                else
                {
                    ($good == TRUE) ? $this->output(true, 'realm_install_success') : $this->output(true, 'realm_install_warning', 'warning');
                }
            }
            else
            {
                $result = $this->DB->update('pcms_realms', $data, "`id`=".$id);
                if($result === FALSE)
                {
                    $this->output(false, 'realm_update_error');
                    return;
                }
                else
                {
                    ($good == TRUE) ? $this->output(true, 'realm_update_success') : $this->output(true, 'realm_update_warning', 'warning');
                }
            }
        }
        
        // Uninstalling
        elseif($action == 'un-install')
        {
            $id = $_POST['id'];
            $this->load->database( 'DB' );
            $result = $this->DB->delete('pcms_realms', '`id`='.$id.'');
            ($result == TRUE) ? $this->output(true, 'realm_uninstall_success') : $this->output(false, 'realm_uninstall_failed', 'error');
        }
        
        // Nobody knows this requested action
        else
        {
            $this->output(false, 'access_denied_privlages');
        }
    }
 

/*
| ---------------------------------------------------------------
| Method: updates()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to check for CMS updates
*/    
    public function updates()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_access('a');
        
        // Disable error reporting
        $debug = load_class('Debug');
        $debug->error_reporting(FALSE);
        
        // Build our headers
        $handle = fsockopen("www.wilson212.net/updates.php", 80, $errno, $errstr, 3);
        if(!$handle)
        {
            $response = "-1";
        }
        else
        {
            fclose($handle);
            $response = file_get_contents("http://wilson212.net/updates.php");
        }
        
        // Set back our error reporting
        $debug->error_reporting(TRUE);
        
        // return the result
        echo $response;
    }
    
/*
| ---------------------------------------------------------------
| Method: output()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to echo out the result of an
|   action
*/    
    private function output($success, $message, $type = 'error')
    {
        // Load language
        $lang = load_language_file( 'messages' );
        $text = (isset($lang[$message])) ? $lang[$message] : $message;
        
        // Build our Json return
        $return = array();
        
        if($success == TRUE)
        {
            // Remove error tag on success, but allow warnings
            ($type == 'error') ? $type = 'success' : '';
            $return['success'] = true;
            $return['message'] = $text;
            $return['type'] = $type;
        }
        else
        {
            $return['success'] = false;
            $return['message'] = $text;
            $return['type'] = $type;
        }
        
        echo json_encode($return);
    }
    
/*
| ---------------------------------------------------------------
| Method: update_account()
| ---------------------------------------------------------------
|
| Bans a user account
|
*/    
    private function update_account($id, $user)
    {
        // Load our validation and Input library's
        $input = load_class('Input', 'Core');
        
        // Set our variables
        $update['email'] = $input->post('email', TRUE);
        $update['group_id'] = $input->post('group_id', TRUE);
        $password1 = $input->post('password1', TRUE);
        $password2 = $input->post('password2', TRUE);
 
        // Load our DB connections
        $this->DB = $this->load->database('DB');
        $this->RDB = $this->load->database( 'RDB' );
        
        // See if our two arrays are different
        $diff = FALSE;
        foreach($update as $key => $value)
        {
            if($user[$key] != $value)
            {
                $diff = TRUE;
            }
        } 
        
        // Update only if needed
        if($diff == TRUE)
        {
            // Do our account Updates
            $result = $this->DB->update('pcms_accounts', $update, "`id`=".$id);
            if( $result === FALSE )
            {
                $this->output(false, 'account_update_error');
                return;
            }
            elseif(!empty($password1) && !empty($password2))
            {
                goto Password;
            }
            else
            {
                $this->output(true, 'account_update_success');
                return;
            }
        }
        
        // Update pass if needed
        Password:
        {
            if(!empty($password1) && !empty($password2))
            {
                if($password1 == $password2)
                {
                    $this->output(false, 'account_update_success');
                    return;
                }
                else
                {
                    $this->output(false, 'account_update_error');
                    return;
                }
            }  
        }
        
        // No updates
        $this->output(false, 'account_update_nochanges', 'warning');
        return;
    }
}
?>