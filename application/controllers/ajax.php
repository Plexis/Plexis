<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
|---------------------------------------------------------------
|
| Navigation. (user CTRL + f to move quickly)
|---------------------------------------------------------------
| A01 - Users
| A02 - Account
| A03 - Groups
| A04 - Permissions
| A05 - News
| A06 - Settings
| A07 - Realms
| A08 - Vote
| A09 - Modules
| A10 - Templates
| A11 - Onlinelist
| A12 - Console
| A13 - Update
|
*/
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
        parent::__construct(false, false);
        
        // Init a session var
        $this->user = $this->Session->get('user');
    }

/*
| ---------------------------------------------------------------
| A01: users()
| ---------------------------------------------------------------
|
| This method is used for an Ajax request to list users
*/    
    public function users()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_permission('manage_users');
        
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
        $Groups = $this->DB->query("SELECT `group_id`,`title` FROM `pcms_account_groups`")->fetch_array();
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
| A02: account()
| ---------------------------------------------------------------
|
| This method is used for an Ajax request to list users
*/    
    public function account($username = NULL)
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_permission('manage_users');
        
        // If we received POST actions, then process it as ajax
        if(isset($_POST['action']))
        {
            // Get users information. We can use GET because the queries second param will be cleaned
            // by the PDO class when bound to the "?". Build our query
            $query = "SELECT * FROM `pcms_accounts` 
                INNER JOIN `pcms_account_groups` ON 
                pcms_accounts.group_id = pcms_account_groups.group_id 
                WHERE username = ?";
            $user = $this->DB->query( $query, array($username) )->fetch_row();
            $id = $user['id'];
   
            // Make sure the current user has privileges to execute an ajax
            if($user['is_admin'] && $_POST['action'] !== 'account-status')
            {
                $this->check_permission('manage_admins');
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
                    $this->load->model('Ajax_Model', 'model');
                    $this->model->admin_update_account($id, $user);
                    break;  
            }
            return;
        }
    }
    
/*
| ---------------------------------------------------------------
| A03: groups()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to modify account groups
*/  
    public function groups()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_access('sa');
        
        // Check for 'action' posts
        if(isset($_POST['action']))
        {
            // Get input class
            $this->input = load_class('Input');
            
            // Get our action type
            switch($_POST['action']) 
            {
                case "getlist":
                    // Load the Ajax Model
                    $this->load->model("Ajax_Model", "ajax");
                    
                    /* 
                    * Array of database columns which should be read and sent back to DataTables. Use a space where
                    * you want to insert a non-database field (for example a counter or static image)
                    */
                    $cols = array( 'group_id', 'title', 'is_banned', 'is_user', 'is_admin', 'is_super_admin' );
                    
                    /* Indexed column (used for fast and accurate table cardinality) */
                    $index = "group_id";
                    
                    /* DB table to use */
                    $table = "pcms_account_groups";
                    
                    /* Database to use */
                    $dB = "DB";
                    
                    /* Process the request */
                    $output = $this->ajax->process_datatables($cols, $index, $table, $dB);
                    
                    // We need to add a working "manage" link
                    foreach($output['aaData'] as $key => $value)
                    {
                        if($value[5] == 1)
                        {
                            $type = "Super Admin";
                        }
                        elseif($value[4] == 1)
                        {
                            $type = "Admin";
                        }
                        elseif($value[3] == 1)
                        {
                            $type = "Member";
                        }
                        elseif($value[2] == 1)
                        {
                            $type = "Banned";
                        }
                        else
                        {
                            $type = "Guest";
                        }
                        
                        // Build our new output
                        $array = array(
                            0 => $value[0],
                            1 => $value[1],
                            2 => $type,
                            3 => '<a class="edit-button" href="javascript:void(0);" name="'.$value[0].'">Edit Group</a> --  
                                  <a href="'. SITE_URL .'/admin/groups/permissions/'.$value[0].'">Manage Permissions</a>'
                        );
                        
                        // Only allow editing of non super admins
                        if($value[5] == 1) $array[3] = '';
                        
                        // We dont allow deleting of default gruops!
                        if($value[0] > 5) $array[3] .= ' -- <a class="delete-button" href="javascript:void(0);" name="'.$value[0].'">Delete Group</a>';
                        $output['aaData'][$key] = $array;
                    }
                    break;
                    
                case "getgroup":
                    // Load the group
                    $id = $this->input->post('id', TRUE);
                    $query = "SELECT * FROM `pcms_account_groups` WHERE `group_id`=?";
                    $group = $this->DB->query( $query, array($id) )->fetch_row();
                    unset($group['permissions']);
                    
                    // Get our group type
                    $type = 1;
                    if($group['is_admin'])
                    {
                        $type = 3;
                    }
                    elseif($group['is_user'])
                    {
                        $type = 2;
                    }
                    elseif($group['is_banned'])
                    {
                        $type = 0;
                    }
                    
                    $output = array(
                        'id' => $id,
                        'type' => $type,
                        'group' => $group
                    );
                    break;
                    
                case "edit":
                    // Load the group
                    $id = $this->input->post('id', TRUE);
                    $title = $this->input->post('title', TRUE);
                    $type = $this->input->post('group_type', TRUE);
                    
                    // Defaults
                    $a = 1;
                    $u = 1;
                    $b = 0;
                    
                    // Process group type
                    if($type == 2)
                    {
                        $a = 0;
                    }
                    elseif($type == 1)
                    {
                        $a = 0;
                        $u = 0;
                    }
                    elseif($type == 0)
                    {
                        $a = 0;
                        $u = 0;
                        $b = 1;
                    }
                    
                    $data = array(
                        'title' => $title,
                        'is_banned' => $b,
                        'is_user' => $u,
                        'is_admin' => $a,
                    );
                    
                    $result = $this->DB->update("pcms_account_groups", $data, "`group_id`=".$id);
                    ($result == TRUE) ? $this->output(true, 'group_update_success') : $this->output(false, 'group_update_error');
                    return;
                    break;
                    
                case "create":
                    // Load POSTS
                    $title = $this->input->post('title', TRUE);
                    $type = $this->input->post('group_type', TRUE);
                    
                    // Defaults
                    $a = 1;
                    $u = 1;
                    $b = 0;
                    
                    // Process group type
                    if($type == 2)
                    {
                        $a = 0;
                    }
                    elseif($type == 1)
                    {
                        $a = 0;
                        $u = 0;
                    }
                    elseif($type == 0)
                    {
                        $a = 0;
                        $u = 0;
                        $b = 1;
                    }
                    
                    $data = array(
                        'title' => $title,
                        'is_banned' => $b,
                        'is_user' => $u,
                        'is_admin' => $a,
                        'is_super_admin' => 0
                    );
                    
                    $result = $this->DB->insert("pcms_account_groups", $data);
                    ($result == TRUE) ? $this->output(true, 'group_create_success') : $this->output(false, 'group_create_error');
                    return;
                    break;
                
                case "deletegroup":
                    $id = $this->input->post('id', TRUE);
                    $result = $this->DB->delete("pcms_account_groups", "`group_id`=$id");
                    ($result == TRUE) ? $this->output(true, 'group_delete_success') : $this->output(false, 'group_delete_error');
                    return;
                    break;
            }
            echo json_encode($output);
        }
    }
    
/*
| ---------------------------------------------------------------
| A04: permissions()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to save permissions for groups
*/  
    public function permissions()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_access('sa');
        
        // Load the input class
        $Input = load_class('Input');
        $id = $Input->post('id', TRUE);
        
        // Do we have POST action?
        if(isset($_POST['action']) && $_POST['action'] == 'save')
        {
            $i = array();
            foreach($_POST as $item => $val) 
            {
                $key = explode('__', $item);
                if($key[0] == 'perm' && $val != 0) 
                {
                    $i[ $key[1] ] = $val;
                }
            }
            
            // Serialize the data
            $up['permissions'] = serialize($i);
            
            // Determine if our save is a success
            $result = $this->DB->update('pcms_account_groups', $up, "`group_id`=$id");
            ($result === TRUE) ? $this->output(false, 'permissions_save_error') : $this->output(true, 'permissions_save_success');
        }
    }

/*
| ---------------------------------------------------------------
| A05: news()
| ---------------------------------------------------------------
|
| This method is used to list news post via an Ajax request
*/    
    public function news()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_permission('manage_news');
 
        // Check for 'action' posts
        if(isset($_POST['action']))
        {
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
| A06: settings()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to save config files
*/  
    public function settings($type = 'App')
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_permission('manage_site_config');
        
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
| A07: realms()
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
                $this->check_permission('manage_realms');
                $action = "install";
                break;
                
            case "manual-install":
                $this->check_permission('manage_realms');
                $action = "manual-install";
                break;
                
            case "edit":
                $this->check_permission('manage_realms');
                $action = "edit";
                break;
                
            case "un-install":
                $this->check_permission('manage_realms');
                $action = "un-install";
                break;
                
            case "make-default":
                $this->check_permission('manage_realms');
                $action = "make-default";
                break;
                
            case "status":
                $action = "status";
                break;
                
            case "admin":
                $this->check_permission('manage_realms');
                $action = "admin";
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
            $aa = array();
            $key = 0;
            $default = config('default_realm_id');
            
            // Loop, and add options for each realm
            foreach($output['aaData'] as $realm)
            {
                // Easier to write
                $id = $realm[0];
                $aa[] = $id;
                
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
            
            // For cores that dont have a realmist in the DB, we need to manually add these
            foreach($installed as $realm)
            {
                $id = $realm['id'];
                if(!in_array($id, $aa))
                {
                    ++$output["iTotalRecords"];
                    ++$output["iTotalDisplayRecords"];
                    $data[0] = $id;
                    $data[1] = $realm['name'];
                    $data[2] = $realm['address'];
                    $data[3] = $realm['port'];
                    
                    // We CANNOT uninstall the default realm!
                    if($id == $default)
                    {
                        $data[4] = "<font color='green'>Installed</font> - Default Realm";
                        $data[5] = "<a href=\"". SITE_URL ."/admin/realms/edit/".$id."\">Update</a>
                            - <a class=\"un-install\" name=\"".$id."\" href=\"javascript:void(0);\">Uninstall</a>";
                    }
                    else
                    {
                        $data[4] = "<font color='green'>Installed</font>";
                        $data[5] = "<a href=\"". SITE_URL ."/admin/realms/edit/".$id."\">Update</a>
                            - <a class=\"make-default\" name=\"".$id."\" href=\"javascript:void(0);\">Make Default</a>
                            - <a class=\"un-install\" name=\"".$id."\" href=\"javascript:void(0);\">Uninstall</a>";
                    }
                    $output['aaData'][] = $data;
                }
            }

            // Push the output in json format
            echo json_encode($output);
        }
        
        // status
        elseif($action == 'status')
        {
            $Cache = $this->load->library('Cache');
            
            // See if we have cached results
            $result = $Cache->get('ajax_realm_status');
            if($result == FALSE)
            {
                // Set to array
                $result = array();

                // If we are here, then the cache results were expired
                $Debug = load_class('Debug');
                $this->load->helper('Time');
                
                // Build our query
                $query = "SELECT `id`, `name`, `type`, `address`, `port` FROM `pcms_realms`";
                
                // fetch the array of realms
                $realms = $this->DB->query( $query )->fetch_array();
                
                // Loop through each realm, and get its status
                foreach($realms as $key => $realm)
                {

                    // Dont show errors errors
                    $Debug->silent_mode(true);
                    $handle = @fsockopen($realm['address'], $realm['port'], $errno, $errstr, 1.5);
                    $Debug->silent_mode(false);
                    
                    // Set our status var
                    ($handle == FALSE) ? $status = 0 : $status = 1;
                    
                    // Load the wowlib for this realm
                    $wowlib = $this->load->wowlib($realm['id']);

                    // Build our realms return
                    if($status == 1 && $wowlib != FALSE)
                    {
                        $uptime = $this->realm->uptime( $realm['id'] );
                        ($uptime == FALSE) ? $uptime = 'Unavailable' : $uptime = sec2hms($uptime, false);
                        
                        $result[] = array(
                            'id' => $realm['id'],
                            'name' => $realm['name'],
                            'type' => $realm['type'],
                            'status' => $status,
                            'online' => $wowlib->get_online_count(0),
                            'alliance' => $wowlib->get_online_count(1),
                            'horde' => $wowlib->get_online_count(2),
                            'uptime' => $uptime
                        );
                    }
                    else
                    {
                        $result[] = array(
                            'id' => $realm['id'],
                            'name' => $realm['name'],
                            'type' => $realm['type'],
                            'status' => $status,
                            'online' => 0,
                            'alliance' => 0,
                            'horde' => 0,
                            'uptime' => 'Offline'
                        );
                    }
                }
                
                // Cache the results for 2 minutes
                $Cache->save('ajax_realm_status', $result, 120);
            }

            // Push the output in json format
            echo json_encode($result);
        }
        
        // Installing / Editing
        elseif($action == 'install' || $action == 'edit' || $action == 'manual-install')
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
                'password'     => $_POST['ra_password'],
                'urn'          => $_POST['ra_urn']
            );

            
            // Turn off all error reporting, since we use our own, this is easy
            $debug = load_class('Debug');
            $debug->silent_mode(true);
            $good = TRUE;

            // Test our new connections before saving to config
            if( !$this->load->database($cs, FALSE) ) $good = FALSE;
            if( !$this->load->database($ws, FALSE) ) $good = FALSE;
            
            // Re-enable errors
            $debug->silent_mode(false);

            // If manually installing, lets get our unique id
            if($action == 'manual-install')
            {
                $result = $this->realm->realmlist();
                $installed = get_installed_realms();
                if( !empty($result) )
                {
                    $highest = end($result);
                    if( empty($installed) )
                    {
                        $id = $highest['id'] + 1;
                    }
                    else
                    {
                        $high2 = end($installed);
                        ($highest['id'] > $high2['id']) ? $id = $highest['id'] + 1 : $id = $high2['id'] + 1;
                    }
                }
                else
                {
                    ( !empty($installed) ) ? $id = $high2['id'] + 1 : $id = 1;
                }
            }
            
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
            if($action == 'install' || $action == 'manual-install')
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
| A08: vote()
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
                    $this->check_permission('manage_votesites');
        
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
                    $this->check_permission('manage_votesites');

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
                    $this->check_permission('manage_votesites');

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
            // Make sure we arent getting direct accessed, and the user has permission
            $this->check_permission('manage_votesites');
  
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
| A09: modules()
| ---------------------------------------------------------------
|
*/    
    public function modules()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_permission('manage_modules');

        // Check for 'action' posts
        if(isset($_POST['action']))
        {
            // Get our action type
            switch($_POST['action']) 
            {
                // CREATING
                case "install":
                    // Load the Modules Model
                    $this->load->model("Admin_Model", "model");
            
                    // Load the Form Validation script
                    $this->load->library('validation');
                    
                    // Tell the validator that the username and password must NOT be empty
                    $this->validation->set( array('uri' => 'required', 'function' => 'required', 'module' => 'required') );
                    
                    // If both the username and password pass validation
                    if( $this->validation->validate() == TRUE )
                    {
                        $result = $this->model->install_module($_POST['module'], $_POST['uri'], $_POST['function']);
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
                    // Load the Modules Model
                    $this->load->model("Admin_Model", "model");
                    
                    $result = $this->model->uninstall_module($_POST['name']);
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
| A10: templates()
| ---------------------------------------------------------------
|
*/    
    public function templates()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_permission('manage_templates');
        
        // Load the input class
        if(!isset($this->input)) $this->input = load_class('Input');

        // Check for 'action' posts
        if(isset($_POST['action']))
        {
            // Get our template id
            $id = $this->input->post('id', TRUE);
            
            // Get our action type
            switch($_POST['action']) 
            {
                // INSTALLING
                case "install":
                    // Make sure we are using an ID here
                    if(!is_numeric($id)) return;
                    
                    // Load the Templates Model
                    $this->load->model("Admin_Model", "model");

                    $result = $this->model->install_template($id);
                    ($result == TRUE) ? $this->output(true, 'template_install_success') : $this->output(false, 'template_install_error');
                    break;
                
                // UNINSTALL
                case "un-install":
                    // Make sure we are using an ID here
                    if(!is_numeric($id)) return;
                    
                    // Get our default Template ID
                    $default = config('default_template');
                    
                    // Dont allow the default template to be uninstalled!
                    $query = "SELECT `name` FROM `pcms_templates` WHERE `id`=?";
                    $name = $this->DB->query( $query, array($id) )->fetch_column();
                    if($default == $default)
                    {
                        $this->output(false, 'template_uninstall_default_warning', 'warning');
                        return;
                    }
                    
                    // Load the Templates Model
                    $this->load->model("Admin_Model", "model");
                    
                    $result = $this->model->uninstall_template($id);
                    ($result == TRUE) ? $this->output(true, 'template_uninstall_success') : $this->output(false, 'template_uninstall_error');
                    break;
                    
                case "make-default":
                    // Load our config class
                    $Config = load_class('Config');
                    
                    // Make sure we are using an ID here
                    if(!is_numeric($id)) return;
                    
                    // Get the template name
                    $query = "SELECT `name` FROM `pcms_templates` WHERE `id`=?";
                    $name = $this->DB->query( $query, array($id) )->fetch_column();
                    
                    // Set the new default Realm
                    $Config->set('default_template', $name, 'App');
                    $result = $Config->save('App');
                    ($result == TRUE) ? $this->output(true, 'template_default_success') : $this->output(false, 'template_default_failed', 'error');
                    break;
                    
                case "getlist":
                    // Load the Ajax Model
                    $this->load->model("Ajax_Model", "ajax");
                    
                    /* 
                    * Array of database columns which should be read and sent back to DataTables. Use a space where
                    * you want to insert a non-database field (for example a counter or static image)
                    */
                    $cols = array( 'name', 'type', 'author', 'status', 'id' );
                    
                    /* Indexed column (used for fast and accurate table cardinality) */
                    $index = "name";
                    
                    /* DB table to use */
                    $table = "pcms_templates";
                    
                    /* Database to use */
                    $dB = "DB";
                    
                    /* Process the request */
                    $output = $this->ajax->process_datatables($cols, $index, $table, $dB);
                    
                    // Get our default Template ID
                    $default = config('default_template');
                    
                    // Get a list of all template folders
                    $list = scandir( APP_PATH . DS . 'templates' );

                    // Loop, and add options
                    foreach($output['aaData'] as $key => $value)
                    {
                        // Easier to write
                        $id = $value[4];
                        $output['aaData'][$key][4] = "";
                        
                        // Make sure the template still exists
                        if(!in_array($value[0], $list))
                        {
                            $this->DB->delete('pcms_templates', "`id`=$id");
                            unset($output['aaData'][$key]);
                            --$output["iTotalRecords"];
                            --$output["iTotalDisplayRecords"];
                            continue;
                        }

                        // Check for default template
                        if($default == $value[0])
                        {
                            // Default?
                            if($value[3] == 1)
                            {
                                $output['aaData'][$key][3] = "<font color='green'>Installed</font> - Default Template";
                                $output['aaData'][$key][4] .= "<a class=\"un-install\" name=\"".$id."\" href=\"javascript:void(0);\">Uninstall</a>";
                            }
                            else
                            {
                                $output['aaData'][$key][3] = "<font color='red'>Not Installed</font>";
                                $output['aaData'][$key][4] .= "<a class=\"install\" name=\"".$id."\" href=\"javascript:void(0);\">Install</a>";
                            }
                        }
                        else
                        {
                            // Default?
                            if($value[3] == 1)
                            {
                                $output['aaData'][$key][4] .= "<a class=\"make-default\" name=\"".$id."\" href=\"javascript:void(0);\">Make Default</a> - ";
                                $output['aaData'][$key][4] .= "<a class=\"un-install\" name=\"".$id."\" href=\"javascript:void(0);\">Uninstall</a>";
                            }
                            else
                            {
                                $output['aaData'][$key][3] = "<font color='red'>Not Installed</font>";
                                $output['aaData'][$key][4] .= "<a class=\"install\" name=\"".$id."\" href=\"javascript:void(0);\">Install</a>";
                            }
                        }
                    }

                    // Push the output in json format
                    echo json_encode($output);
                    break;
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| A11: onlinelist()
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
| A12: console()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to handle remote access commands
*/    
    public function console()
    {
        // Check access
        $this->check_permission('send_console_commands');

        // Make sure there is post data!
        if( !isset($_POST['action']) ) return;
        
        // Load the input cleaner
        $this->input = load_class('Input');
        
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
                // Grab our realm info
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
                $uri = ( !empty($ra['urn']) ) ? $ra['urn'] : NULL;
            }
            else
            {
                $info = explode(' ', $this->input->post('overide', TRUE));
                $user = $this->input->post('user', TRUE);
                $pass = $this->input->post('pass', TRUE);
                $host = $info[0];
                $port = $info[1];
                $type = ucfirst( strtolower($info[2]) );
                (isset($info[3]) && strlen( trim($info[3]) ) > 1) ? $uri = trim($info[3]) : $uri = NULL;
            }
            
            // Load the RA class
            $ra = $this->load->library( $type );
            
            // Try and log the user in
            $result = $ra->connect($host, $port, $user, $pass, $uri);
            
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
| A13: update()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to update the cms
*/    
    public function update()
    {
        // Make sure we arent directly accessed and the user has perms
        $this->check_access('sa');
        
        if(isset($_POST['action']))
        {
            $this->input = load_class('input');
            $action = trim( $this->input->post('action') );
            
            switch($action)
            {
                case "get_latest":
                    // Make sure the Openssl extension is loaded
                    if(!extension_loaded('openssl'))
                    {
                        echo json_encode( array('success' => false, 'message' => 'Openssl extension not found. Please enable the openssl extension in your php.ini file') );
                        return;
                    }
                    
                    // Check for https support
                    if(!in_array('https', stream_get_wrappers()))
                    {
                        echo json_encode( array('success' => false, 'message' => 'Unable to find the stream wrapper "https" - did you forget to enable it when you configured PHP?') );
                        return;
                    }
                    
                    // Make sure the client server allows fopen of urls
                    if(ini_get('allow_url_fopen') == 1)
                    {
                        // Get the file changes from github
                        $start = microtime(1);
                        load_class('Debug')->silent_mode(true);
                        $page = trim( file_get_contents('https://api.github.com/repos/Plexis/Plexis/commits?per_page=1', false) );
                        load_class('Debug')->silent_mode(false);
                        $stop = microtime(1);
                        
                        if($page == FALSE || empty($page))
                        {
                            echo json_encode( array('success' => false, 'message' => 'Unable to connect to the update server') );
                            return;
                        }
                        
                        // Decode the results
                        $commits = json_decode($page, TRUE);
                        
                        // Defaults
                        $count = 0;
                        $latest = 0;

                        echo json_encode( array('success' => true, 'message' => $commits) );
                        return;
                    }
                    else
                    {
                        echo json_encode( array('success' => false, 'message' => 'allow_url_fopen not enabled in php.ini') );
                        return;
                    }
                    break;
                    
                case "init":
                    config_set('site_updating', 1);
                    config_save('app');
                    break;
                    
                case "finish":
                    config_set('site_updating', 0);
                    config_save('app');
                    break;
                    
                case "next":
                    $url = $type = trim( $this->input->post('url') );
                
                    // Get the file changes from github
                    $start = microtime(1);
                    load_class('Debug')->silent_mode(true);
                    $page = trim( file_get_contents($url, false) );
                    load_class('Debug')->silent_mode(false);
                    $stop = microtime(1);
                    
                    if($page == FALSE || empty($page))
                    {
                        echo json_encode( array('success' => false, 'data' => 'Error fetching updates') );
                        return;
                    }
                    
                    echo json_encode( array('success' => true, 'data' => json_decode($page)) );
                    break;
                
                case "update":
                    // Grab POSTS
                    $type = trim( $this->input->post('status') );
                    $sha = trim( $this->input->post('sha') );
                    $url = trim( $this->input->post('raw_url') );
                    $file = trim( $this->input->post('filename') );
                    $adds = trim( $this->input->post('additions') );
                    $dels = trim( $this->input->post('deletions') );
                    $filename = ROOT . DS . str_replace(array('/','\\'), DS, $file);
                    $dirname = dirname($filename);
                    
                    // Build our default Json return
                    $return = array();
                    $success = TRUE;
                    $removed = FALSE;
                    
                    // Get file contents
                    $contents = file_get_contents($url, false);
                    
                    // Hush errors
                    load_class('Debug')->silent_mode(true);
                    switch($type)
                    {
                        case "renamed":
                            // We need to get the old filename, unfortunatly, github API doesnt supply a way to do this
                            $hurl = "https://github.com/Plexis/Plexis/commits/".$sha."/".$file;
                            $src = preg_replace( "#[\r\n\t\s]#", "", file_get_contents( $hurl, false ) );
                            $reg = "#Filerenamedfrom\<code\>\<ahref=\"(.*?)\"\>(.*?)\</a\>\</code\>#i";
                            $count = preg_match_all( $reg, $src, $matches, PREG_SET_ORDER );
                            if($count)
                            {
                                $removed = TRUE;
                                $old_file = ROOT . DS . str_replace(array('/','\\'), DS, $matches[0][2]);
                                unlink($old_file);
                            }
                            else
                            {
                                $success = FALSE;
                                $text = 'Error moving/renaming file "'. $file .'"';
                                goto Output;
                            }
                            // Do not Break!
                        case "added":

                            // We need to check for a soft file rename( file "added", no additions, no files removed )
                            if($adds == 0 && $dels == 0)
                            {
                                // File sha probably is different then the commit sha
                                if(preg_match( "#/([0-9a-z]{40})/#i", $url, $matches))
                                {
                                    $parts = explode($matches[1], $url);
                                    $parts[1] = trim($parts[1], '/');
                                    if($parts[1] != $file)
                                    {
                                        // Soft rename indeed.. remove the old damn file >:(
                                        unlink(ROOT . DS . $parts[1]);
                                        $removed = TRUE;
                                    }
                                }
                            }
                            
                            // Continue as normal
                            if(!is_dir($dirname))
                            {
                                // Create the directory for the new file if it doesnt exist
                                $create = @mkdir($dirname, 0755, true);
                                if(!$create && !is_dir($dirname))
                                {
                                    $success = FALSE;
                                    $text = 'Error creating directory "'. $dirname .'"';
                                    goto Output;
                                }
                            }
                            // Do not Break!
                        case "modified":
                            $handle = @fopen($filename, 'w+');
                            if($handle)
                            {
                                // Write the new file contents to the file
                                $fwrite = @fwrite($handle, $contents);
                                if($fwrite === FALSE)
                                {
                                    $success = FALSE;
                                    $text = 'Error writing to file "'. $file .'"';
                                    goto Output;
                                }
                                @fclose($handle);
                            }
                            else
                            {
                                $success = FALSE;
                                $text = 'Error opening / creating file "'. $file .'"';
                                goto Output;
                            }
                            break;
                            
                        case "removed":
                            $removed = TRUE;
                            unlink($filename);
                            break;
                    } // End witch type
                    
                    // Removed empty dirs
                    if($removed == TRUE)
                    {
                        $handle = @opendir($dirname);
                        if($handle)
                        {
                            $files = array();
                            $empty = TRUE;
                            
                            // Run each file / dir to determine if empty
                            while($file = readdir($handle))
                            {
                                $files[] = $file;
                                if($file[0] != ".")
                                {
                                    if(is_file($path . DS . $file) || is_dir($path . DS . $file))
                                    {
                                        $empty = FALSE;
                                        break;
                                    }
                                }
                            }
                            @closedir($handle);
                            
                            // If empty, delete .DS / .htaccess files and remove dir!
                            if($empty)
                            {
                                foreach($files as $file)
                                {
                                    // Removed .DS && .htaccess files
                                    if($file == "." || $file == "..") continue;
                                    unlink($file);
                                }
                                @rmdir($dirname);
                            }
                        }
                    }
                    
                    // Output goto
                    Output:
                    {
                        load_class('Debug')->silent_mode(false);
                        if($success == TRUE)
                        {
                            // Remove error tag on success, but allow warnings
                            ($type == 'error') ? $type = 'success' : '';
                            $return['success'] = true;
                        }
                        else
                        {
                            $return['success'] = false;
                            $return['message'] = $text;
                        }
                        
                        echo json_encode($return);
                    }
                break;

            } // End Swicth $action
        }
        echo "no action";
    }
    
/*
| ---------------------------------------------------------------
| METHODS
| ---------------------------------------------------------------
*/
    
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
        if(($lvl == 'a' || $lvl == 'sa') && $this->user['is_admin'] != 1)
        {
            if($this->user['is_super_admin'] != 1)
            {
                $this->output(false, 'access_denied_privlages');
                die();
            }
        }
        elseif($lvl == 'u' && !$this->user['is_loggedin'])
        {
            $this->output(false, 'access_denied_privlages');
            die();
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: check_permission()
| ---------------------------------------------------------------
|
| This method is used for certain Ajax pages to see if the requester
|   has permission to process the request.
|
| @Param: $perm - The name of the permission
*/   
    protected function check_permission($perm)
    {
        // Make sure the user has admin access'
        if( !$this->Auth->has_permission($perm))
        {
            $this->output(false, 'access_denied_privlages');
            die();
        }
    }
 
/*
| ---------------------------------------------------------------
| Method: output()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to echo out the result of an
|   action
*/    
    public function output($success, $message, $type = 'error')
    {
        // Load language
        $lang = load_language_file( 'messages' );
        $text = (isset($lang[$message])) ? $lang[$message] : $message;
        
        // Build our Json return
        $return = array();
        
        if($success == TRUE)
        {
            // Remove error tag on success, but allow warnings
            if($type == 'error') $type = 'success';
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
}
?>