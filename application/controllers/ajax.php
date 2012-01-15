<?php
class Ajax extends Application\Core\Controller 
{
    // Our user
    protected $user;

/*
| ---------------------------------------------------------------
| Constructor
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
|   has permission to receive the request.
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
        
        // If we received POST actions, then process it as ajax
        if(isset($_POST['action']))
        {
            // Load the database
            $this->load->database( 'DB' );
            
            // Get users information. We can use GET because the queries second param will be cleaned
            // by the PDO class when bound to the "?".
            $query = "SELECT * FROM `pcms_accounts` WHERE `username` LIKE ?";
            $user = $this->DB->query( $query, array($username) )->fetch_row();
            $id = $user['id'];
   
            // Make sure the current user has privileges to execute an ajax
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
                
            case "make-default":
                $this->check_access('a');
                $action = "make-default";
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

            // We need to add a working "manage", and "Make Default Link" link
            $key = 0;
            $default = config('default_realm_id');
            
            // Loop, and add options
            foreach($output['aaData'] as $realm)
            {
                // Easier to write
                $id = $realm[0];
                
                // Create out action links for this realm
                if(in_array($id, $irealms))
                {
                    // We CANNOT uninstall the default realm!
                    if($id == $default)
                    {
                        $output['aaData'][$key][4] = "<font color='green'>Installed</font> - Default Realm";
                        $output['aaData'][$key][5] = "<a href=\"". SITE_URL ."/admin/realms/edit/".$id."\">Update</a>
                            - <a class=\"un-install\" name=\"".$id."\" href=\"javascript:void(0);\">Uninstall</a>";
                    }
                    else
                    {
                         $output['aaData'][$key][4] = "<font color='green'>Installed</font>";
                        $output['aaData'][$key][5] = "<a href=\"". SITE_URL ."/admin/realms/edit/".$id."\">Update</a>
                            - <a class=\"make-default\" name=\"".$id."\" href=\"javascript:void(0);\">Make Default</a>
                            - <a class=\"un-install\" name=\"".$id."\" href=\"javascript:void(0);\">Uninstall</a>";
                    }
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
            
            // Build our DB Arrays
            $cs = array(
                'driver'	   => $_POST['c_driver'],
                'host'         => $_POST['c_address'],
                'port'         => $_POST['c_port'],
                'username'     => $_POST['c_username'],
                'password'     => $_POST['c_password'],
                'database'     => $_POST['c_database']
            );
            $ws = array(
                'driver'	   => $_POST['w_driver'],
                'host'         => $_POST['w_address'],
                'port'         => $_POST['w_port'],
                'username'     => $_POST['w_username'],
                'password'     => $_POST['w_password'],
                'database'     => $_POST['w_database']
            );
            $ra = array(
                'type'         => $_POST['ra_type'],
                'port'         => $_POST['ra_port'],
                'username'     => $_POST['ra_username'],
                'password'     => $_POST['ra_password']
            );

            
            // Turn off all error reporting, since we use our own, this is easy
            $debug = load_class('Debug');
            $debug->error_reporting(FALSE);
            $good = TRUE;

            // Test our new connections before saving to config
            try{
                $dba = new \pdo( 
                    $cs['driver'].':host='.$cs['host'].':'.$cs['port'].';dbname='.$cs['database'], 
                    $cs['username'], 
                    $cs['password'],
                    array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
                );
            }
            catch(\PDOException $e){
                $good = FALSE;
            }
            
            // Test our new connection first
            try{
                $dba = new \pdo( 
                    $ws['driver'].':host='.$ws['host'].':'.$ws['port'].';dbname='.$ws['database'], 
                    $ws['username'], 
                    $ws['password'],
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
                'char_db' => serialize($cs),
                'world_db' => serialize($ws),
                'ra_info' => serialize($ra),
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
                    // Set as default realm if we dont have one
                    if($Config->get('default_realm_id') == 0)
                    {
                        // Set the new default Realm
                        $Config->set('default_realm_id', $data['id'], 'App');
                        $Config->save('App');
                    }
                    ($good == TRUE) ? $this->output(true, 'realm_install_success') : $this->output(true, 'realm_install_warning', 'warning');
                }
            }
            else
            {
                // Update the realms table
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
            // Load our config class
            $Config = load_class('Config');
            
            // Get our realm ID and the default realm ID
            $id = $_POST['id'];
            $default = $Config->get('default_realm_id');
            
            // Run the delete though the database
            $this->load->database( 'DB' );
            $result = $this->DB->delete('pcms_realms', '`id`='.$id.'');
            
            // If we are uninstalling the default Realm, we set a new one
            if($id == $default)
            {
                // Get the new Default Realm
                $installed = get_installed_realms();
                
                if($installed == FALSE || empty($installed))
                {
                    // Set the new default Realm
                    $Config->set('default_realm_id', 0, 'App');
                    $Config->save('App');
                }
                else
                {
                    // Set the new default Realm
                    $Config->set('default_realm_id', $installed[0]['id'], 'App');
                    $Config->save('App');
                }
            }
            ($result == TRUE) ? $this->output(true, 'realm_uninstall_success') : $this->output(false, 'realm_uninstall_failed', 'error');
        }
        
        // Making default
        elseif($action == 'make-default')
        {
            // Load our config class
            $Config = load_class('Config');
            
            // Get our realm ID
            $id = $_POST['id'];
            
            // Set the new default Realm
            $Config->set('default_realm_id', $id, 'App');
            $result = $Config->save('App');
            ($result == TRUE) ? $this->output(true, 'realm_default_success') : $this->output(false, 'realm_default_failed', 'error');
        }
        
        // Nobody knows this requested action
        else
        {
            $this->output(false, 'access_denied_privlages');
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: news()
| ---------------------------------------------------------------
|
| This method is used to list news post via an Ajax request
*/    
    public function vote()
    {
        // Check for 'action' posts
        if(isset($_POST['action']))
        {
            // Load the News Model
            $this->load->model('Vote_Model', 'model');
        
            // Get our action type
            switch($_POST['action']) 
            {
                // CREATING
                case "create":
                    // Make sure we arent getting direct accessed, and the user has permission
                    $this->check_access('a');
            
                    // Load the Form Validation script
                    $this->load->library('validation');
                    
                    // Tell the validator that the username and password must NOT be empty
                    $this->validation->set( array(
                        'honstname' => 'required', 
                        'votelink' => 'required')
                    );
                    
                    // If both the username and password pass validation
                    if( $this->validation->validate() == TRUE )
                    {
                        $result = $this->model->create($_POST['hostname'], $_POST['votelink'], $_POST['image_url'], $_POST['points'], $_POST['reset_time']);
                        ($result == TRUE) ? $this->output(true, 'votesite_created_successfully') : $this->output(false, 'votesite_create_failed');
                    }
                    
                    // Validation failed
                    else
                    {
                        $this->output(false, 'form_validation_failed');
                    }
                    break;
                
                // EDITING
                case "edit":
                    // Make sure we arent getting direct accessed, and the user has permission
                    $this->check_access('a');
            
                    // Load the Form Validation script
                    $this->load->library('validation');
                    
                    // Tell the validator that the username and password must NOT be empty
                    $this->validation->set( array(
                        'honstname' => 'required', 
                        'votelink' => 'required')
                    );
                    
                    // If both the username and password pass validation
                    if( $this->validation->validate() == TRUE )
                    {
                        $result = $this->model->update($_POST['id'], $_POST['hostname'], $_POST['votelink'], $_POST['image_url'], $_POST['points'], $_POST['reset_time']);
                        ($result == TRUE) ? $this->output(true, 'votesite_update_success') : $this->output(false, 'votesite_update_error', 'warning');
                    }
                    
                    // Validation failed
                    else
                    {
                        $this->output(false, 'form_validation_failed');
                    }
                    break;
                    
                // DELETING
                case "delete":
                    // Make sure we arent getting direct accessed, and the user has permission
                    $this->check_access('a');
                    $result = $this->model->delete($_POST['id']);
                    ($result == TRUE) ? $this->output(true, 'votesite_delete_success') : $this->output(false, 'votesite_delete_error');
                    break;
                    
                // VOTING
                case "vote":
                    $result = $this->model->submit($this->user['id'], $_POST['site_id']);
                    ($result == TRUE) ? $this->output(true, 'vote_submit_success') : $this->output(false, 'vote_submit_error');
                    break;
                 
                // STATUS
                case "status":
                    $site = $this->model->get_vote_site($_POST['site_id']);
                    $result = get_port_status($site['hostname'], 80, 2);
                    $this->output($result, $site['votelink']);
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
            $cols = array( 'id', 'hostname', 'votelink', 'points', 'reset_time' );
            
            /* Indexed column (used for fast and accurate table cardinality) */
            $index = "id";
            
            /* DB table to use */
            $table = "pcms_vote_sites";
            
            /* Database to use */
            $dB = "DB";
            
            /* Process the request */
            $output = $this->ajax->process_datatables($cols, $index, $table, $dB);
            
            // We need to add a working "manage" link
            foreach($output['aaData'] as $key => $value)
            {
                $output['aaData'][$key][4] = ($output['aaData'][$key][4] == 43200) ? "12 Hours" : "24 Hours";
                $output['aaData'][$key][5] = '<a href="'. SITE_URL .'/admin/vote/edit/'.$value[0].'">Edit</a> 
                    - <a class="delete" name="'.$value[0].'" href="javascript:void(0);">Delete</a>';
            }
            
            // Push the output in json format
            echo json_encode($output);
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: modules()
| ---------------------------------------------------------------
|
*/    
    public function modules()
    {
        // Check for 'action' posts
        if(isset($_POST['action']))
        {
            // Get our action type
            switch($_POST['action']) 
            {
                // CREATING
                case "install":
                    // Make sure we arent getting direct accessed, and the user has permission
                    $this->check_access('a');
                    
                    // Load the Modules Model
                    $this->load->model("Modules_Model", "model");
            
                    // Load the Form Validation script
                    $this->load->library('validation');
                    
                    // Tell the validator that the username and password must NOT be empty
                    $this->validation->set( array('uri' => 'required', 'function' => 'required', 'module' => 'required') );
                    
                    // If both the username and password pass validation
                    if( $this->validation->validate() == TRUE )
                    {
                        $result = $this->model->install($_POST['module'], $_POST['uri'], $_POST['function']);
                        ($result == TRUE) ? $this->output(true, 'module_install_success') : $this->output(false, 'module_install_error');
                    }
                    
                    // Validation failed
                    else
                    {
                        $this->output(false, 'form_validation_failed');
                    }
                    break;
                
                // UNINSTALL
                case "un-install":
                    // Make sure we arent getting direct accessed, and the user has permission
                    $this->check_access('a');
                    
                    // Load the Modules Model
                    $this->load->model("Modules_Model", "model");
                    
                    $result = $this->model->uninstall($_POST['name']);
                    ($result == TRUE) ? $this->output(true, 'module_uninstall_success') : $this->output(false, 'module_uninstall_error');
                    break;
                    
                case "getlist":
                    // Load the Ajax Model
                    $this->load->model("Ajax_Model", "ajax");
                    
                    /* 
                    * Array of database columns which should be read and sent back to DataTables. Use a space where
                    * you want to insert a non-database field (for example a counter or static image)
                    */
                    $cols = array( 'name', 'uri', 'method', 'has_admin' );
                    
                    /* Indexed column (used for fast and accurate table cardinality) */
                    $index = "name";
                    
                    /* DB table to use */
                    $table = "pcms_modules";
                    
                    /* Database to use */
                    $dB = "DB";
                    
                    /* Process the request */
                    $output = $this->ajax->process_datatables($cols, $index, $table, $dB);
                    
                    // Get our NON installed modules
                    $mods = get_modules();

                    // Loop, and add options
                    foreach($output['aaData'] as $key => $module)
                    {
                        // Easier to write
                        $name = $module[0];
                        $key = array_search($name, $mods);
                        unset( $mods[ $key ] );

                        $output['aaData'][$key][3] = "<font color='green'>Installed</font>";
                        $output['aaData'][$key][4] = "<a class=\"un-install\" name=\"".$name."\" href=\"javascript:void(0);\">Uninstall</a>";
                        
                        // Add admin link
                        if($module[3] == TRUE) $output['aaData'][$key][4] .= " - <a href=\"". SITE_URL ."/admin/modules/manage/".$name."\">Configure</a>"; 
                    }

                    $i = 0;
                    foreach($mods as $mod)
                    {
                        ++$i;
                        $output['aaData'][] = array(
                            0 => $mod,
                            1 => "N/A",
                            2 => "N/A",
                            3 => "<font color='red'>Not Installed</font>",
                            4 => "<a class=\"install\" name=\"".$mod."\" href=\"javascript:void(0);\">Install</a>"
                        );
                    }
                    
                    // add totals
                    $output["iTotalRecords"] = $i + $output["iTotalRecords"];
                    $output["iTotalDisplayRecords"] = $i + $output["iTotalDisplayRecords"];
                    
                    // Push the output in json format
                    echo json_encode($output);
                    break;
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: onlinelist()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to get the players online
*/    
    public function onlinelist($realm = 0)
    {
        // check if the realm is installed
        if($realm != 0 && realm_installed($realm) == FALSE)
        {
            $realm = get_realm_cookie();
        }
        // Load the WoWLib
        $this->load->wowlib($realm, 'wowlib');
        $output = $this->wowlib->get_online_list_datatables();
        
        // Loop, and add options
        foreach($output['aaData'] as $key => $value)
        {
            $g = $value[5];
            $r = $value[3];
            $race = $this->wowlib->race_to_text($r);
            $class = $this->wowlib->class_to_text($value[4]);
            $zone = $this->wowlib->zone_to_text($value[6]);
            $output['aaData'][$key][3] = '<img src="'. SITE_URL .'/application/static/images/icons/race/'. $r .'-'. $g .'.gif" title="'.$race.'" alt="'.$race.'">';
            $output['aaData'][$key][4] = '<img src="'. SITE_URL .'/application/static/images/icons/class/'. $value[4] .'.gif" title="'.$class.'" alt="'.$class.'">';
            $output['aaData'][$key][5] = $zone;
            unset($output['aaData'][$key][6]);
        }

        // Push the output in json format
        echo json_encode($output);
    }
    
/*
| ---------------------------------------------------------------
| Method: console()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to handle remote access commands
*/    
    public function console()
    {
        // Make sure there is post data!
        if( !isset($_POST['action']) ) return;
        
        // Load the input cleaner
        $this->input = load_class('Input');
        
        // Check Access
        $this->check_access('a');
        
        // Defaults
        $action = trim( $this->input->post('action', TRUE) );

        // Proccess our action
        if($action == 'init')
        {
            $output['status'] = 200;
            $output['show'] = "<br /><center>---- WELCOME TO THE PLEXIS REMOTE ACCESS TERMINAL ----</center><br />";
            $output['show'] .= "<span class=\"c_keyword\"> In this window, you are able to type in server commands which are sent directly to your server.</span><br />";
            $output['show'] .= "<span class=\"c_keyword\"> Please login using your Remote Access Credentials before sending commands (See console commands)</span><br />";
        }
        elseif($action == 'command')
        {
            $overide = $this->input->post('overide', TRUE);
            if( empty($overide) )
            {
                // Grab our real info
                $id = $this->input->post('realm', TRUE);
                $realm = get_realm($id);
                if($realm == FALSE)
                {
                    $output = array(
                        'status' => 300,
                        'show' => 'This realm is not installed!'
                    );
                    goto Output;
                }
                
                // Load out Remote access info's
                $ra = unserialize($realm['ra_info']);
                $port = $ra['port'];
                $type = ucfirst( strtolower($ra['type']) );
                $user = $this->input->post('user', TRUE);
                $pass = $this->input->post('pass', TRUE);
                $host = $realm['address'];
            }
            else
            {
                $info = explode(' ', $this->input->post('overide', TRUE));
                $user = $this->input->post('user', TRUE);
                $pass = $this->input->post('pass', TRUE);
                $host = $info[0];
                $port = $info[1];
                $type = ucfirst( strtolower($info[2]) );
            }
            
            // Load the RA class
            $ra = $this->load->library( $type );
            
            // Try and log the user in
            $result = $ra->connect($host, $port, $user, $pass);
            
            // Go no further if Auth failed
            if($result == FALSE)
            {
                // Prepare output
                $response = $ra->get_response();
                $output['status'] = 300;
                $output['show'] = $response;
                
                // Disconnect
                $ra->disconnect();
                goto Output;
            }
            
            // Default vars
            $command = trim( $this->input->post('command', TRUE) );
            $command = ltrim($command, '.');
            $comm = explode(' ', $command);
            $type = trim($comm[0]);
            
            // Process
            switch($type)
            {
                case "login":
                    // If we are here, then we are good!
                    $output['status'] = 200;
                    $output['show'] = "Logged In Successfully";
                    break;
                    
                default:
                    $send = $ra->send($command);
                    ($send != FALSE) ? $output['status'] = 200 : $output['status'] = 400;
                    $output['show'] = $ra->get_response();
                    break;
            }
            
            // Disconnect
            $ra->disconnect();
        }
        else
        {
            $output = array(
                'status' => 100,
                'show' => 'Invalid POST Action'
            );
        }

        // Push the output in json format
        Output: { echo json_encode($output); }
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
		
		//Set up our variables.
        $update['email'] = $input->post('email', TRUE);
        $update['group_id'] = $input->post('group_id', TRUE);
		$password = array( $input->post('password1', TRUE), $input->post('password2', TRUE) );
		$expansion = $input->post('expansion', TRUE);
		$game_account = $this->realm->fetch_account($id);
 
        // Load our DB connections
        $this->DB = $this->load->database('DB');
        $this->RDB = $this->load->database( 'RDB' );
		
		$changed = FALSE; //Have there been any changes to the data?
		$game_changed = FALSE; //Whether or not the game account has some info edited.
		
		foreach( $update as $key => $value )
		{
			if( $user[$key] != $value )
				$changed = true;
			
			if( !empty($password[0]) && !empty($password[1]) && !$game_changed )
				$game_changed = true;
				
			if( $expansion != $game_account['expansion'] && !$game_changed )
				$game_changed = true;
		}
        
		if( $game_changed || $changed )
		{
			if( $changed )
			{
				$result = $this->DB->update("pcms_accounts", $update, "`id` = '$id'");
				
				if( $result === FALSE )
				{
					$this->output(false, 'account_update_error');
					return;
				}
			}
			
			if( $game_changed )
			{
				if( !empty($password[0]) && !empty($password[1]) )
				{
					if( $password[0] == $password[1] )
					{
						$result = $this->realm->change_password($id, $password[0]);
						
						if( !$result )
						{
							$this->output(false, 'account_update_error');
							return;
						}
					}
					else
					{
						$this->output(false, 'account_update_error');
						return;
					}
				}
				
				if( $expansion != $game_account['expansion'] )
				{
					$result = $this->realm->update_expansion($expansion, $id);
					
					if( !$result )
					{
						$this->output(false, 'account_update_error');
						return;
					}
				}
			}
			
			$this->output(true, 'account_update_success');
			return;
		}
        
        // No updates
        $this->output(false, 'account_update_nochanges', 'warning');
        return;
    }
}
?>