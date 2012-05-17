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
| A02 - Account
| A03 - Groups
| A04 - Permissions
| A05 - News
| A06 - Settings
| A07 - Characters
| A08 - Realms
| A09 - Vote
| A10 - Modules
| A11 - Templates
| A12 - Console
| A13 - Regkeys
| A14 - Logs
| A19 - Update
|
*/
class Admin_ajax extends Application\Core\Controller 
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
        
        // Load the input class since we are always using that here!
        $this->input = load_class('Input');
        
        // Make sure user is an admin!
        if( !$this->check_permission('admin_access') );
        
        // Make sure the request is an ajax request, and came from this website!
        if(!$this->input->is_ajax())
        {
            die('No direct linking allowed');
        }
        elseif(strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false)
        {
            $this->output(false, 'Unauthorized');
            die();
        }
    }

/*
| ---------------------------------------------------------------
| A02: accounts()
| ---------------------------------------------------------------
|
| This method is used for an Ajax request to list users, and preform
| account actions
*/    
    public function accounts()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_permission('manage_users');
        
        // If we received POST actions, then process it as ajax
        if(isset($_POST['action']))
        {
            // Get our user id
            $id = $this->input->post('id');
            
            // Get users information. We can use GET because the queries second param will be cleaned
            // by the PDO class when bound to the "?". Build our query
            $query = "SELECT * FROM `pcms_accounts` 
                INNER JOIN `pcms_account_groups` ON 
                pcms_accounts.group_id = pcms_account_groups.group_id 
                WHERE id = ?";
            $user = $this->DB->query( $query, array($id) )->fetch_row();
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
                    // Make sure this person has permission to do this!
                    ($user['is_admin'] == 1) ? $this->check_permission('ban_admin_account') : $this->check_permission('ban_user_account');
                    
                    // Log action
                    $this->log('Banned user account: '. $user['username']);
                    
                    // Set our variables
                    $date = strtotime($this->input->post('unbandate', true));
                    $reason = $this->input->post('banreason', true);
                    $banip = (isset($_POST['banip'])) ? $this->input->post('banip', true) : FALSE;
                    
                    // Process the Ban, and process the return result
                    $result = $this->realm->ban_account($id, $reason, $date, $this->user['username'], $banip);
                    ($result == TRUE) ? $this->output(true, 'account_ban_success') : $this->output(false, 'account_ban_error');
                    break;
                    
                case "unban-account":
                    // Make sure this person has permission to do this!
                    ($user['is_admin'] == 1) ? $this->check_permission('ban_admin_account') : $this->check_permission('ban_user_account');
                    
                    // Log action
                    $this->log('Un-Banned user account '. $user['username']);
                    
                    // Unban the account
                    $result = $this->realm->unban_account($id);
                    ($result == TRUE) ? $this->output(true, 'account_unban_success') : $this->output(false, 'account_unban_error');
                    break;
                    
                case "lock-account":
                    // Log action
                    $this->log('Locked user account '. $user['username']);
                    
                    // Lock account
                    $result = $this->realm->lock_account($id);
                    ($result == TRUE) ? $this->output(true, 'account_lock_success') : $this->output(false, 'account_lock_error');
                    break;
                    
                case "unlock-account":
                    // Log action
                    $this->log('Un-Locked user account '. $user['username']);
                    
                    // Unlock the account
                    $result = $this->realm->unlock_account($id);
                    ($result == TRUE) ? $this->output(true, 'account_unlock_success') : $this->output(false, 'account_unlock_error');
                    break;
                    
                case "delete-account":
                    // Make sure this person has permission to do this!
                    ($user['is_admin'] == 1) ? $this->check_permission('delete_admin_account') : $this->check_permission('delete_user_account');
                    
                    // Continue
                    if( $this->realm->delete_account($id) )
                    {
                        // Log action
                        $this->log('Deleted user account '. $user['username']);
                    
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
        else
        {
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
            
            /* where statment */
            $where = '';
            
            /* Process the request */
            $output = $this->ajax->process_datatables($cols, $index, $table, $where, $this->DB);
            
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
            
            // Get our action type
            switch($_POST['action']) 
            {
                case "getlist":
                    // Load the Ajax Model
                    $this->load->model("Ajax_Model", "ajax");
                    
                    /// Perpare DataTables data
                    $cols = array( 'group_id', 'title', 'is_banned', 'is_user', 'is_admin', 'is_super_admin' );
                    $index = "group_id";
                    $table = "pcms_account_groups";
                    $where = '';
                    
                    /* Process the request */
                    $output = $this->ajax->process_datatables($cols, $index, $table, $where, $this->DB);
                    
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
                    
                    // Log action
                    $this->log('Edited Group ID: '. $id);
                    
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
                    
                case "create":
                    // Load POSTS
                    $title = $this->input->post('title', TRUE);
                    $type = $this->input->post('group_type', TRUE);
                    
                    // Log action
                    $this->log('Created new group: '. $title);
                    
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
                
                case "deletegroup":
                    $id = $this->input->post('id', TRUE);
                    
                    // Get the group title
                    $query = "SELECT `title` FROM `pcms_groups` WHERE `group_id`=?";
                    $title = $this->DB->query($query, array($id))->fetch_column();
                    
                    // Log action
                    $this->log('Deleted user group "'. $title .'"');
                    $result = $this->DB->delete("pcms_account_groups", "`group_id`=$id");
                    ($result == TRUE) ? $this->output(true, 'group_delete_success') : $this->output(false, 'group_delete_error');
                    return;
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
        $id = $this->input->post('id', TRUE);
        
        // Get the group title
        $query = "SELECT `title` FROM `pcms_account_groups` WHERE `group_id`=?";
        $title = $this->DB->query($query, array($id))->fetch_column();
        
        // Log action
        $this->log('Edited permissions for group "'. $title .'"');
        
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
                        // Log action
                        $this->log('Published news post "'. $this->input->post('title', true) .'"');
                        $result = $this->News_Model->submit_news($_POST['title'], $_POST['body'], $this->user['username']);
                        ($result == TRUE) ? $this->output(true, 'news_posted_successfully') : $this->output(false, 'news_post_error');
                    }
                    
                    // Validation failed
                    else
                    {
                        $this->output(false, 'news_validation_error');
                    }
                    break;
                    
                // FETCHING
                case "get":
                    $result = $this->News_Model->get_news_post( $this->input->post('id', true) );
                    if($result == FALSE)
                    {
                        $this->output(false, 'News Item Doesnt Exist!');
                        die();
                    }
                    
                    // Fix body!
                    $result['body'] = stripslashes($result['body']);
                    $this->output(true, $result);
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
                        // Get the news title
                        $query = "SELECT `title` FROM `pcms_news` WHERE `id`=?";
                        $title = $this->DB->query($query, array( $this->input->post('id', true) ))->fetch_column();
                        
                        // Log action
                        $this->log('Modified news post with title: "'. $title .'"');
                    
                        // Preform the action
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
                    // Get the news title
                    $query = "SELECT `title` FROM `pcms_news` WHERE `id`=?";
                    $title = $this->DB->query($query, array( $this->input->post('id', true) ))->fetch_column();
                    
                    // Log action
                    $this->log('Deleted news post with title: "'. $title .'"');
                    
                    // Preform the delete
                    $result = $this->News_Model->delete_post( $this->input->post('id', true) );
                    ($result == TRUE) ? $this->output(true, 'news_delete_success') : $this->output(false, 'news_delete_error');
                    break;
            }
        }
        else
        {
            // Load the Ajax Model
            $this->load->model("Ajax_Model", "ajax");
            
            // Prepare DataTables
            $cols = array( 'id', 'title', 'author', 'posted' );
            $index = "id";
            $table = "pcms_news";
            $where = '';
            
            /* Process the request */
            $output = $this->ajax->process_datatables($cols, $index, $table, $where, $this->DB);
            
            // We need to add a working "manage" link
            foreach($output['aaData'] as $key => $value)
            {
                $output['aaData'][$key][3] = date("F j, Y, g:i a", $output['aaData'][$key][3]);
                $output['aaData'][$key][4] = '<a class="edit" name="'.$value[0].'" href="javascript:void(0);">Edit</a>
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
        
        // Log action
        $this->log('Modified site config settings');
        
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
| A07: characters()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to get the players online
*/    
    public function characters($realm = 0)
    {
        // If not post action, we are loading the list
        if(!isset($_POST['action']))
        {
            // check if the realm is installed
            if($realm != 0 && !realm_installed($realm))
            {
                $realm = get_realm_cookie();
            }

            // Load the WoWLib
            $this->load->wowlib($realm, 'wowlib');
            if(is_object($this->wowlib))
            {
                $output = $this->wowlib->characters->get_character_list_datatables();
                
                // Loop, each character, and format the rows accordingly
                foreach($output['aaData'] as $key => $value)
                {
                    $u = $this->realm->fetch_account($value[7]);
                    $g = $value[5];
                    $r = $value[3];
                    $race = $this->wowlib->characters->race_to_text($r);
                    $class = $this->wowlib->characters->class_to_text($value[4]);
                    $zone = $this->wowlib->zone->name($value[6]);
                    $output['aaData'][$key][3] = '<center><img src="'. SITE_URL .'/application/static/images/icons/race/'. $r .'-'. $g .'.gif" title="'.$race.'" alt="'.$race.'"></center>';
                    $output['aaData'][$key][4] = '<center><img src="'. SITE_URL .'/application/static/images/icons/class/'. $value[4] .'.gif" title="'.$class.'" alt="'.$class.'"></center>';
                    $output['aaData'][$key][5] = $zone;
                    $output['aaData'][$key][6] = '<a href="'. SITE_URL .'/admin/users/'. $u['username'] .'">'. $u['username'] .'</a>';
                    $output['aaData'][$key][7] = '<a href="'. SITE_URL .'/admin/characters/'. $realm .'/'. $value[0] .'">Edit Character</a>';
                    unset($output['aaData'][$key][8]);
                }
                
                // Push the output in json format
                echo json_encode($output);
                return;
            }
            else
            {
                // Unable to load Wowlib
                $this->output(false, 'Failed to load Wowlib. Please make sure the character / world Databases are online');
            }
        }
        else
        {
            // Mandaroty checks
            $id = $this->input->post('id', true);
            $realm = $this->input->post('realm', true);
            
            // Make sure the realm is installed
            if( !realm_installed($realm) )
            {
                $this->output(false, "Realm ID: $realm not installed!");
                die();
            }
    
            // Load the wowlib for this realm
            $Lib = $this->load->wowlib($realm, false);
            if($Lib == false)
            {
                $this->output(false, "Unable to connect to character and/or world databases");
                die();
            }
            
            // Fetch character
            $char = $Lib->characters->get_character_info($id);
            if($char == false)
            {
                $this->output(false, "Character ID: Does not exist!");
                die();
            }
            
            // Make sure the character isnt online
            if($char['online'] == 1)
            {
                $this->output(false, "Character is online. You cannot edit characters while they are in game.", 'warning');
                die();
            }
            
            // Process specific actions
            switch($_POST['action'])
            {
                case "update":
                    // Make sure this person has permission to do this!
                    $this->check_permission('manage_characters');
                    
                    // Get our realm name
                    $r = get_realm($realm);
                    
                    // Log action
                    $this->log('Edited character '. $char['name'] .' from realm '. $r['name']);
                    
                    // Update the character data
                    $info = array(
                        'name' => $this->input->post('name', true),
                        'level' => $this->input->post('level', true),
                        'gender' => $this->input->post('gender', true),
                        'money' => $this->input->post('money', true),
                        'xp' => $this->input->post('xp', true)
                    );
                    $result = $Lib->set_character_info($id, $info);
                    
                    // make sure we didnt fail here
                    if($result === false)
                    {
                        $this->output(false, "There was an error updating the character information. Please check your error logs");
                        die();
                    }
                    
                    // Get the characters flags
                    $change = false;
                    $flags = $Lib->get_login_flags($id);
                    foreach($flags as $key => $val)
                    {
                        // Updates?
                        if(isset($_POST[ $key ]) && (bool)$_POST[ $key ] != $val)
                        {
                            $change = true;
                            $Lib->set_login_flag($id, $key, $_POST[ $key ]);
                        }
                    }
                    
                    // Any successfull changes?
                    if($result === 0 && $change == false)
                    {
                        $this->output(false, "Character data not updated. This may be due to no changes being made.", 'warning');
                    }
                    else
                    {
                        $this->output(true, "Character updated successfully!");
                    }
                    break;
                    
                case "delete":
                    // Make sure this person has permission to do this!
                    $this->check_permission('delete_characters');
                    
                    // Get our realm name
                    $r = get_realm($realm);
                    
                    // Log action
                    $this->log('Deleted character '. $char['name'] .' from realm '. $r['name']);
                    $result = $Lib->delete_character( $this->input->post('id', true) );
                    ($result == true) ? $this->output(true, "Character deleted successfully!") : $this->output(false, "There was an error deleting the character!");
                    break;
                    
                case "unstuck":
                    // Make sure this person has permission to do this!
                    $this->check_permission('manage_characters');
                    $this->output(false, "This feature is incomplete.", 'warning');
                    break;
                    
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| A08: realms()
| ---------------------------------------------------------------
|
| This method is used to update / install realms via Ajax
*/    
    public function realms()
    {
        // Load the ajax model and action
        $this->load->model('Ajax_Model', 'model');
        $action = $this->input->post('action');
        
        // Make sure use has perms
        $this->check_permission('manage_realms');

        // Find out what we are doing here by our requested action
        switch($action)
        {
            case "install": 
            case "manual-install":
            case "edit":
                $this->model->process_realm($action);
                break;
                
            case "un-install":
                // Get our realm name
                $r = get_realm($realm);
                
                // Log action
                $this->log('Uninstalled Realm '. $r['name']);
                $this->model->uninstall_realm();
                break;
                
            case "make-default":
                // Get our realm name
                $r = get_realm($realm);
                
                // Log action
                $this->log('Changed the default realm to '. $r['name']);
                
                // Load our config class
                $Config = load_class('Config');
                
                // Get our realm ID
                $id = $this->input->post('id', true);
                
                // Set the new default Realm
                $Config->set('default_realm_id', $id, 'App');
                $result = $Config->save('App');
                ($result == TRUE) ? $this->output(true, 'realm_default_success') : $this->output(false, 'realm_default_failed', 'error');
                break;
                
            case "admin":
                
                // Prepare DataTables
                $cols = array( 'id', 'name', 'address', 'port' );
                $index = "id";
                $table = "realmlist";
                $where = '';
                
                /* Process the request */
                $output = $this->model->process_datatables($cols, $index, $table, $where, $this->RDB);
                
                
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
                break;
                
            default:
                $this->output(false, "Unknown Action");
                break;
        }
    }
    
/*
| ---------------------------------------------------------------
| A09: vote()
| ---------------------------------------------------------------
|
| This method is used to list news post via an Ajax request
*/    
    public function vote()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_permission('manage_votesites');
 
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
                        // Log action
                        $this->log('Created a new votesite with host "'. $_POST['hostname'] .'"');
                        $result = $this->model->create($_POST['hostname'], $_POST['votelink'], $_POST['image_url'], $_POST['points'], $_POST['reset_time']);
                        ($result == TRUE) ? $this->output(true, 'votesite_created_successfully') : $this->output(false, 'votesite_create_failed');
                    }
                    
                    // Validation failed
                    else
                    {
                        $this->output(false, 'form_validation_failed');
                    }
                    break;
                
                // FETCHING
                case "get":
                    // Get our votesite
                    $data = $this->model->get_vote_site( $this->input->post('id', true) );
                    ( $data == false ) ? $this->output(false, 'Votesite Doesnt Exist!') : $this->output(true, $data);
                    break;
                
                // EDITING
                case "edit":
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
                        // Log action
                        $this->log('Modified votesite with ID: '. $this->input->post('id', true));
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
                    // Log action
                    $this->log('Deleted votesite ID: '. $this->input->post('id', true));

                    $result = $this->model->delete($this->input->post('id', true));
                    ($result == TRUE) ? $this->output(true, 'votesite_delete_success') : $this->output(false, 'votesite_delete_error');
                    break;
            }
        }
        else
        {
            // Load the Ajax Model
            $this->load->model("Ajax_Model", "ajax");
            
            // Prepare DataTables
            $cols = array( 'id', 'hostname', 'votelink', 'points', 'reset_time' );
            $index = "id";
            $table = "pcms_vote_sites";
            $where = '';
            
            /* Process the request */
            $output = $this->ajax->process_datatables($cols, $index, $table, $where, $this->DB);
            
            // We need to add a working "manage" link
            foreach($output['aaData'] as $key => $value)
            {
                $output['aaData'][$key][4] = ($output['aaData'][$key][4] == 43200) ? "12 Hours" : "24 Hours";
                $output['aaData'][$key][5] = '<a class="edit" name="'.$value[0].'" href="javascript:void(0);">Edit</a> 
                    - <a class="delete" name="'.$value[0].'" href="javascript:void(0);">Delete</a>';
            }
            
            // Push the output in json format
            echo json_encode($output);
        }
    }
    
/*
| ---------------------------------------------------------------
| A10: modules()
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
                        // Log action
                        $this->log('Installed module "'. $this->input->post('module', true) .'"');
                        $result = $this->model->install_module($this->input->post('module', true), $this->input->post('uri', true), $this->input->post('function', true));
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
                    
                    // Log action
                    $this->log('Uninstalled module "'. $this->input->post('name', true) .'"');
                    
                    $result = $this->model->uninstall_module( $this->input->post('name', true) );
                    ($result == TRUE) ? $this->output(true, 'module_uninstall_success') : $this->output(false, 'module_uninstall_error');
                    break;
                    
                case "getlist":
                    // Load the Ajax Model
                    $this->load->model("Ajax_Model", "ajax");
                    
                    // Prepare DataTables
                    $cols = array( 'name', 'uri', 'method', 'has_admin' );
                    $index = "name";
                    $table = "pcms_modules";
                    $where = '';

                    /* Process the request */
                    $output = $this->ajax->process_datatables($cols, $index, $table, $where, $this->DB);
                    
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
                        if($module[3] == TRUE) $output['aaData'][$key][4] .= " - <a href=\"". SITE_URL ."/admin/modules/".$name."\">Configure</a>"; 
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
| A11: templates()
| ---------------------------------------------------------------
|
*/    
    public function templates()
    {
        // Make sure we arent getting direct accessed, and the user has permission
        $this->check_permission('manage_templates');

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
                    
                    // Log action
                    $this->log('Installed template "'. $id .'"');
                    
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
                    if($default == $name)
                    {
                        $this->output(false, 'template_uninstall_default_warning', 'warning');
                        return;
                    }
                    
                    // Log action
                    $this->log('Uninstalled template "'. $name .'"');
                    
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
                    
                    // Log action
                    $this->log('Changed default template to "'. $name .'"');
                    
                    // Set the new default Realm
                    $Config->set('default_template', $name, 'App');
                    $result = $Config->save('App');
                    ($result == TRUE) ? $this->output(true, 'template_default_success') : $this->output(false, 'template_default_failed', 'error');
                    break;
                    
                case "getlist":
                    // Load the Ajax Model
                    $this->load->model("Ajax_Model", "ajax");
                    
                    // Prepare DataTables
                    $cols = array( 'name', 'type', 'author', 'status', 'id' );
                    $index = "name";
                    $table = "pcms_templates";
                    $where = '';
                    
                    /* Process the request */
                    $output = $this->ajax->process_datatables($cols, $index, $table, $where, $this->DB);
                    
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
| A13: regkeys()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to generate Reg keys
*/  
    public function regkeys()
    {
        // Assign our mode varaible
        $mode = $_POST['action'];

        // Process key creation first.
        if( $mode == "generate" )
        {
            // Load the account model
            $this->load->model('account_model', 'model');
        
            // Let's generate the key.
            $new_key = $this->model->create_invite_key($this->user['id']);
            ($new_key !== false) ? $this->output(true, $new_key) : $this->output(false, 'Error creating invite key. Please check your error logs');
        }
        
        // Process key deletion next.
        if( $mode == "delete" )
        {
            $key_query = $this->DB->delete('pcms_reg_keys', '`usedby` = 0'); //Get the key.
            
            // Check to make sure the query didn't fail.
            if( $key_query !== FALSE )
            {
                $this->output(true, 'Unassigned keys deleted successfully!');
                die();
            }
            
            $this->output(false, 'No Keys Deleted');
        }
    }
    
/*
| ---------------------------------------------------------------
| A14: logs()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to get the admin / error logs
*/  
    public function logs()
    {
        $type = $this->input->post('type', true);
        switch($_POST['action'])
        {
            case "get":
                // Load the Ajax Model
                $this->load->model("Ajax_Model", "ajax");
   
                if($type == 'errors')
                {
                    // Make sure this person has permission to do this!
                    $this->check_permission('manage_error_logs');
                    
                    // Prepare DataTables
                    $cols = array( 'id', 'level', 'string', 'file', 'line' );
                    $index = "id";
                    $table = "pcms_error_logs";
                    $where = '';
                    
                    /* Process the request */
                    $output = $this->ajax->process_datatables($cols, $index, $table, $where, $this->DB);
                    foreach($output['aaData'] as $key => $value)
                    {
                        $output['aaData'][$key][5] = '<a href="javascript:void(0);" class="delete" name="'. $value[0] .'">Delete</a>';
                    }
                    
                    echo json_encode($output); return;
                }
                else
                {
                    // Make sure this person has permission to do this!
                    $this->check_permission('view_admin_logs');
                    
                    // Prepare DataTables
                    $cols = array( 'id', 'username', 'desc', 'time' );
                    $index = "id";
                    $table = "pcms_admin_logs";
                    $where = '';
                    
                    /* Process the request */
                    $output = $this->ajax->process_datatables($cols, $index, $table, $where, $this->DB);
                    foreach($output['aaData'] as $key => $value)
                    {
                        $output['aaData'][$key][3] = date('F j, g:i:s a', strtotime($value[3]));
                        $output['aaData'][$key][4] = '<a href="javascript:void(0);" class="delete" name="'. $value[0] .'">Delete</a>';
                    }
                    
                    echo json_encode($output); return;
                }
                break;
                
            case "details":
                break;
                
            case "delete":
                // Get our ID and table type
                $id = $this->input->post('id', true);
                if($type == 'errors')
                {
                    // Make sure this person has permission to do this!
                    $this->check_permission('manage_error_logs');
                    $table = 'error';
                }
                else
                {
                    // Make sure this person has permission to do this!
                    $this->check_permission('delete_admin_logs');
                    $table = 'admin';
                }
                
                // Check for deletion of all logs
                if($id == 'all')
                {
                    $result = $this->DB->exec("TRUNCATE `pcms_{$table}_logs`");
                    if($result === false) 
                    {
                        // Try a normal delete
                        $result = $this->DB->delete('pcms_'. $table .'_logs');
                    }
                    else
                    {
                        $result = true;
                    }
                }
                else
                {
                    $result = $this->DB->delete('pcms_'. $table .'_logs', "`id`=$id");
                }
                ($result == TRUE) ? $this->output(true, 'Successfully Deleted Log Entry.') : $this->output(false, 'Failed to delete log entry! Please check your error log.');
                break;
        }
    }
    
/*
| ---------------------------------------------------------------
| A19: update()
| ---------------------------------------------------------------
|
| This method is used for via Ajax to update the cms
*/    
    public function update()
    {
        // Make sure we arent directly accessed and the user has perms
        $this->check_access('sa');
        
        // Load the URL helper
        $this->load->helper('Url');
        
        if(isset($_POST['action']))
        {
            $action = trim( $this->input->post('action') );
            
            switch($action)
            {
                case "get_latest":
                    // cURL exist? If not we need to verify the user has openssl installed and https support
                    $curl = function_exists('curl_exec');
                    if(!$curl)
                    {
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
                    }
                    
                    // Make sure the client server allows fopen of urls
                    if(ini_get('allow_url_fopen') == 1 || $curl == true)
                    {
                        // Get the file changes from github
                        $start = microtime(1);
                        load_class('Debug')->silent_mode(true);
                        $page = getPageContents('https://api.github.com/repos/Plexis/Plexis/commits?per_page=1', false);
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
                    // Load the commit.info file
                    $sha = $type = trim( $this->input->post('sha') );
                    $url = 'https://raw.github.com/Plexis/Plexis/'. $sha .'/commit.info';
                
                    // Get the file changes from github
                    $start = microtime(1);
                    load_class('Debug')->silent_mode(true);
                    $page = trim( getPageContents($url, false) );
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
                    $file = trim( str_replace(array('/', '\\'), '/', $this->input->post('filename')) );
                    $url = 'https://raw.github.com/Plexis/Plexis/'. $sha .'/'. $file;
                    $filename = ROOT . DS . str_replace('/', DS, $file);
                    $dirname = dirname($filename);
                    
                    // Load our Filesystem Class
                    $Fs = $this->load->library('Filesystem');
                    
                    // Build our default Json return
                    $return = array();
                    $success = TRUE;
                    $removed = FALSE;
                    
                    // Hush errors
                    load_class('Debug')->silent_mode(true);
                    
                    // Get file contents
                    $contents = trim( getPageContents($url, false) );
                    $mod = substr($type, 0, 1);
                    switch($mod)
                    {
                        case "R":
                            break;
                            
                        case "A":

                            // Make sure the Directory exists!
                            if(!is_dir($dirname))
                            {
                                // Create the directory for the new file if it doesnt exist
                                if( !$Fs->create_dir($dirname) )
                                {
                                    $this->output(false, 'Error creating directory "'. $dirname .'"');
                                    return;
                                }
                            }
                            // Do not Break!
                        case "M":
                            $handle = @fopen($filename, 'w+');
                            if($handle)
                            {
                                // Write the new file contents to the file
                                $fwrite = @fwrite($handle, $contents);
                                if($fwrite === FALSE)
                                {
                                    $this->output(false, 'Error writing to file "'. $file .'"');
                                    return;
                                }
                                @fclose($handle);
                            }
                            else
                            {
                                $this->output(false, 'Error creating / writting to file "'. $file .'"');
                                return;
                            }
                            break;
                            
                        case "D":
                            $removed = TRUE;
                            $Fs->delete($filename);
                            break;
                    }
                    
                    // Removed empty dirs
                    if($removed == TRUE)
                    {
                        // Re-read the directory
                        clearstatcache();
                        $files = $Fs->read_dir($dirname);

                        // If empty, delete .DS / .htaccess files and remove dir!
                        if(empty($files) || (sizeof($files) == 1 && $files[0] == '.htaccess'))
                        {
                            $Fs->remove_dir($dirname);
                        }
                    }
                    
                    // Output success
                    $this->output(true, '');
                break;

            } // End Swicth $action
        }
        else
        {
            $this->output(false, 'Invalid Post Action');
        }
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
| Method: log()
| ---------------------------------------------------------------
|
| This method is used to log actions preformed by admins
|
| @Param: $message - The message to be saved
*/   
    protected function log($message)
    {
        log_action($this->user['username'], $message);
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
        // Load language only if a string, with no spaces is offered
        if(!empty($message) && !is_array($message) && strpos($message, ' ') === false)
        {
            $lang = load_language_file( 'messages' );
            $message = (isset($lang[$message])) ? $lang[$message] : $message;
        }
        
        // Build our Json return
        $return = array();
        
        // Remove error tag on success, but allow warnings
        if($success == TRUE && $type == 'error') $type = 'success';
        
        // Output to the browser in Json format
        echo json_encode(
            array(
                'success' => $success,
                'message' => $message,
                'type' => $type
            )
        );
    }
}
?>