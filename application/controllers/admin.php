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
| P01 - Dashboard
| P02 - Php Info Page
| P03 - News Managment
| P04 - Manage Accounts
| P05 - Site Settings
| P06 - Groups and Permissions
| P07 - Registration Settings
| P08 - Realm Management
| P09 - Vote
| P10 - Modules
| P11 - Templates
| P12 - Console
| P13 - Update
| P14 - Logs
|
*/
class Admin extends Application\Core\Controller 
{
    public function __construct()
    {
        // Build the Core Controller
        parent::__construct();
        
        // Init a session var
        $this->user = $this->Session->get('user');
        
        // Make sure the user has admin access'
        if( !$this->Auth->has_permission('admin_access') )
        {
            redirect( SITE_URL );
            die();
        }
    }

/*
| ---------------------------------------------------------------
| P01: Dashboard
| ---------------------------------------------------------------
|
*/
    public function index() 
    {
        // Get our PHP and DB versions
        $info = $this->DB->server_info();
        $rewrite = (isset($_SERVER['HTTP_MOD_REWRITE']) && $_SERVER['HTTP_MOD_REWRITE'] == 'On') ? 'On' : 'Off';
        
        // Add our build var
        $this->Template->setjs('Build', CMS_BUILD);
        
        // Set our page data
        $data = array(
            'page_title' => "Dashboard",
            'page_desc' => "Here you have a quick overview of some features",
            'driver' => ucfirst( $info['driver'] ),
            'php_version' => phpversion(),
            'mod_rewrite' => $rewrite,
            'database_version' => $info['version'],
            'CMS_VERSION' => CMS_VERSION,
            'CMS_BUILD' => CMS_BUILD,
            'CMS_DB_VERSION' => CMS_DB_VERSION
        );
        
        // Load the page, and we are done :)
        $this->load->view('dashboard', $data);
    }

/*
| ---------------------------------------------------------------
| P02: PHPinfo Page
| ---------------------------------------------------------------
|
*/ 
    public function phpinfo($plain = FALSE) 
    {
        if($plain == 'html')
        {
            echo phpinfo();
        }
        else
        {
            // Set our page title and desc
            $data['page_title'] = "Php Info";
            $data['page_desc'] = "You are viewing this servers phpinfo";
            
            // Load the page, and we are done :)
            $this->load->view('phpinfo', $data);
        }
    }

/*
| ---------------------------------------------------------------
| P03: News Managment Page
| ---------------------------------------------------------------
|
*/ 
    public function news()
    {
        // Make sure the user can view this page
        if( !$this->check_access('manage_news')) return;

        // Load our editor script
        $this->Template->add_script( BASE_URL .'/application/admin/js/tiny_mce/jquery.tinymce.js' );

        // Build our page variable data
        $data = array(
            'page_title' => "Manage News",
            'page_desc' => "From here, you can Edit, Delete, or create a new news post."
        );
        
        // Load the view
        $this->load->view('news', $data);
    }

/*
| ---------------------------------------------------------------
| P04: Manage Users
| ---------------------------------------------------------------
|
*/    
    public function users($username = NULL)
    {
        // Make sure the user can view this page
        if( !$this->check_access('manage_users')) return;

        // No Username, Build the index page
        if($username == NULL)
        {
            // Build our page title / desc, then load the view
            $data = array(
                'page_title' => "Manage Users",
                'page_desc' => "Here you can manage the account of all your users."
            );
            $this->load->view('users_index', $data);
        }
        
        // We have a username, Load the user
        else
        {
            // Get users information. We can use GET because the queries second param will be cleaned
            // by the PDO class when bound to the "?".
            $query = "SELECT * FROM `pcms_accounts` INNER JOIN `pcms_account_groups` ON 
                pcms_accounts.group_id = pcms_account_groups.group_id WHERE `username` = ?";
            $user = $this->DB->query( $query, array($username) )->fetch_row();
            
            // If $user isnt an array, we failed to load the user
            if(!is_array($user))
            {
                // Load the page, and we are done :)
                output_message('error', 'user_not_found_1');
                
                // Build our page title / desc, then load the view
                $data = array(
                    'page_title' => "Loading",
                    'page_desc' => "Please wait while we redirect you..."
                );
                redirect('admin/users', 5);
                $this->load->view('redirect', $data);
            }
            else
            {
                // Use the realm database to grab user information first
                $user2 = $this->realm->fetch_account($user['id']);
				$data['expansion_data'] = $this->realm->get_expansion_info();
                
                // Use the additional inforamation from the realm DB
                if($user2 !== FALSE)
                {
                    // Determine out Account status
                    $status = $this->realm->account_banned($user['id']);
                    if($status == FALSE)
                    {
                        // Set ban status to Ban
                        $data['account_ban_button'] = "ban";
                        $data['account_ban_button_text'] = "Ban Account";
                        
                        // Load lock status
                        if($user2['locked'] == FALSE)
                        {
                            $user['status'] = 'Active';
                            $data['account_lock_button'] = "lock";
                            $data['account_lock_button_text'] = "Lock Account";
                        }
                        else
                        {
                            $user['status'] = 'Locked';
                            $data['account_lock_button'] = "unlock";
                            $data['account_lock_button_text'] = "UnLock Account";
                        }
                    }
                    else
                    {
                        $user['status'] = 'Banned';
                        $data['account_ban_button'] = "unban";
                        $data['account_ban_button_text'] = "UnBan Account";
                        $data['account_lock_button'] = "lock";
                        $data['account_lock_button_text'] = "Lock Account";
                    }
                    $user = array_merge($user2, $user);
                    
                    // Finish Building our data array
                    $data['page_title'] = ucfirst( strtolower($username) )." (Account ID: ".$user['id'].")";
                    $data['page_desc'] = "Here you can manage the account of all your users.";
                    $data['user'] = $user;
                    $data['groups'] = $this->DB->query("SELECT * FROM `pcms_account_groups`")->fetch_array();
                    
                    // Load the view
                    $this->load->view('user_manage', $data);
                }
                else
                {
                    // Load the page, and we are done :)
                    output_message('error', 'user_not_found_2');
                    
                    // Build our page title / desc, then load the view
                    $data = array('page_title' => "", 'page_desc' => "");
                    
                    // Load the error page, no redirect
                    $this->load->view('redirect', $data);
                    return;
                }
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| P05: Site Settings
| ---------------------------------------------------------------
|
*/    
    public function settings()
    {
        // Make sure the user can view this page
        if( !$this->check_access('manage_site_config')) return;

        // Load our config class
        $Config = load_class('Config');
        
        // Use admin model to process and make our "select option" fields
        $this->load->model('Admin_model', 'model');
        $options = $this->model->site_settings_options();
        
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Site Settings",
            'page_desc' => "Here you can manage the account of all your users.",
            'config' => $Config->get_all('app'),
            'options' => $options
        );
        $this->load->view('site_settings', $data);
    }

/*
| ---------------------------------------------------------------
| P06: User Groups and Permissions
| ---------------------------------------------------------------
|
*/     
    public function groups($sub1 = NULL, $id = NULL)
    {
        // Load our config & Input class
        $Config = load_class('Config');
        $Input = load_class('Input');

        // Make sure user is super admin for ajax
        if($this->user['is_super_admin'] != 1)
        {
            $this->show_403();
            return;
        }
        
        if($sub1 != NULL && $id != NULL)
        {
            // Clean the ID
            $id = $Input->clean($id);
            switch($sub1)
            {
                case "permissions":
                    
                    // Load the perms for this group
                    $query = "SELECT * FROM `pcms_account_groups` WHERE `group_id`=?";
                    $group = $this->DB->query( $query, array($id) )->fetch_row();
                    $perms = unserialize($group['permissions']);
                    unset($group['permissions']); 
                    if($perms == FALSE) $perms = array();
                    
                    // Get all permissions
                    $query = "SELECT * FROM `pcms_permissions`";
                    $array = $this->DB->query( $query, array($id) )->fetch_array();
                    $changed = FALSE;
                    $list = array();
                    foreach($array as $key => $p)
                    {
                        if( !isset($perms[$p['key']]) )
                        {
                            $changed = TRUE;
                            $perms[$p['key']] = 0;
                        }
                        $list[$p['key']] = $p;
                    }
                    unset($array);
                    
                    // For ordering purposes
                    $permissions = array('admin' => array(), 'core' => array());
                    $sections = array('admin', 'core');
                    foreach($perms as $key => $p)
                    {
                        if(!isset($list[$key]))
                        {
                            $changed = TRUE;
                            unset($perms[$key]); 
                            continue;
                        }
                        $g = $list[$key]['module'];
                        $permissions[$g][$key] = $p;
                        if(!in_array($g, $sections)) $sections[] = $g;
                    }
                    
                    // Update as need be
                    if($changed == TRUE)
                    {
                        // Only insert values of 1
                        $update = array();
                        foreach($perms as $key => $value)
                        {
                            if($value == 1) $update[$key] = $value;
                        }
                        $i['permissions'] = serialize($update);
                        $this->DB->update('pcms_account_groups', $i, "`group_id`=$id");
                    }
                    
                    // Build our page title / desc, then load the view
                    $data = array(
                        'page_title' => "Group Permissions",
                        'page_desc' => "Editting Permissions",
                        'group' => $group,
                        'permissions' => $permissions,
                        'list' => $list,
                        'sections' => $sections
                    );
                    $this->load->view('group_permissions', $data);
                break;
            }
            return;
        }

        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "User Groups & Permissions",
            'page_desc' => "On this page, you can Create / Delete user groups and ajust site permission on a group basis."
        );
        $this->load->view('groups', $data);
    }

/*
| ---------------------------------------------------------------
| P07: Registration Settigns
| ---------------------------------------------------------------
|
*/    
    public function registration()
    {
        // Make sure the user can view this page
        if( !$this->check_access('manage_site_config')) return;

        // Load our config class
        $Config = load_class('Config');
        
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Registration Settings",
            'page_desc' => "Here you can set the Registration requirements and settings for new accounts. You are also able to generate Invite keys here.",
            'config' => $Config->get_all('App')
        );
        $this->load->view('registration', $data);
    }

/*
| ---------------------------------------------------------------
| P08: Realm Managment
| ---------------------------------------------------------------
|
*/    
    public function realms($subpage = 'index', $id = NULL)
    {
        // Make sure the user can view this page
        if( !$this->check_access('manage_realms')) return;

        // Process our page
        switch($subpage)
        {
            case "index":
                // Build our page title / desc, then load the view
                $data = array(
                    'page_title' => "Realm Managment",
                    'page_desc' => "Here you can Manage your realms, setup Remote Access, and send console commands to your server.",
                );
                $this->load->view('realms_index', $data);
                break;
            
            // EDITING
            case "edit":
                // Make sure we have an id!
                if($id === NULL || !is_numeric($id)) redirect('admin/realms');

                // Load installed drivers
                $drivers = get_wowlib_drivers();
                if($drivers == FALSE) $drivers = array();
                
                // Load our installed realm info
                $realm = $this->DB->query("SELECT * FROM `pcms_realms` WHERE `id`=?", array($id))->fetch_row();

                // Redirect if this realm doesnt exist / isnt installed
                if($realm == FALSE) redirect('admin/realms');
                
                // Unserialize our DB realms connection information
                $realm['cdb'] = unserialize($realm['char_db']);
                $realm['wdb'] = unserialize($realm['world_db']);
                $realm['ra'] = unserialize($realm['ra_info']);
                
                // Build our page title / desc, then load the view
                $data = array(
                    'page_title' => "Edit Realm",
                    'page_desc' => "Here you can change the DB settings for your realm, as well as the driver.",
                    'realm' => $realm,
                    'drivers' => $drivers
                );
                $this->load->view('realms_edit', $data);
                break;
            
            // INSTALL
            case "install":
                // Load installed drivers
                $drivers = get_wowlib_drivers();
                if($drivers == FALSE) $drivers = array();
                
                // Build our page title / desc
                $data = array(
                    'page_title' => "Realm Installation",
                    'page_desc' => "On this page you will be able to install a new realm for use on the site. Installing a realm allows you as well as users to 
                        see statistics about the realm, view online characters, and user character tools such as Character Rename.",
                    'drivers' => $drivers
                );
                
                // check for an existing install
                if($id != NULL)
                {
                    // Make sure the realm isnt already installed
                    $installed = get_installed_realms();
                    $irealms = array();
                    
                    // Build an array of installed IDs
                    foreach($installed as $realm)
                    {
                        $irealms[] = $realm['id'];
                    }
                    if(in_array($id, $irealms)) redirect('admin/realms/edit/'.$id);
                    
                    // Get realm information
                    $realm = $this->realm->fetch_realm($id);
                    
                    // Load the view
                    $data = $data + array('realm' => $realm);
                    $this->load->view('realms_install', $data);
                }
                else
                {
                    $this->load->view('realms_install_manual', $data);
                }
                break;
                
            default:
                redirect('admin/realms');
                break;
        }
    }
    
/*
| ---------------------------------------------------------------
| P09: Vote
| ---------------------------------------------------------------
|
*/
    public function vote()
    {
        // Make sure the user can view this page
        if( !$this->check_access('manage_votesites')) return;

        // Build our page variable data
        $data = array(
            'page_title' => "Manage Vote Sites",
            'page_desc' => "Create, Edit, or Delete vote sites that your users will use to vote for your server."
        );
        
        // Load the view
        $this->load->view('vote', $data);
    }

/*
| ---------------------------------------------------------------
| P10: Modules
| ---------------------------------------------------------------
|
*/    
    public function modules($name = null, $subpage = null)
    {
        // Make sure the user can view this page
        if( !$this->check_access('manage_modules')) return;
        
        if($name != null)
        {
            // Make sure the module is installed!
            if( module_installed($name) )
            {
                // Load the module controller
                $file = APP_PATH . DS . 'modules' . DS . $name . DS .'controller.php';
                if(file_exists($file))
                {
                    // Load the file
                    include $file;

                    // Init the module into a variable
                    $class = ucfirst($name);
                    $module = new $class( true );
                    
                    // Correct the module view path'
                    $this->Template->set_controller($class, true);

                    // Build our page title / desc, then load the view
                    $this->Template->set( 'page_title', $class ." Config");
                    $this->Template->set( 'page_desc', "On this page, you can configure this module.");
                    
                    // Run the module installer
                    $result = $module->__admin( $this, $subpage );
                    if($result == TRUE) die();
                    
                    // We have an error!
                    
                    // Correct the module view path'
                    $this->Template->set_controller('Admin', false);
                    
                    // Build our page title / desc, then load the view
                    $this->Template->set( 'page_title', "Error Loading Module");
                    $this->Template->set( 'page_desc', "");
                    $this->load->view('module_load_error', $data);
                    return;
                }
            }
        }

        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Module Managment",
            'page_desc' => "On this page, you can install and manage your installed modules. You may also edit module config files here.",
        );
        $this->load->view('module_index', $data);
    }

/*
| ---------------------------------------------------------------
| P11: Templates
| ---------------------------------------------------------------
|
*/     
    public function templates()
    {
        // Make sure the user can view this page
        if( !$this->check_access('manage_templates')) return;

        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Template Manager",
            'page_desc' => "This page allows you to manage your templates, which includes uploading, installation, and un-installation.",
        );
        
        // Get installed templates
        $query = "SELECT * FROM `pcms_templates` WHERE `type`='site'";
        $templates = $this->DB->query( $query )->fetch_array();
        foreach($templates as $t)
        {
            $aa[] = $t['name'];
        }
        
        // Scan and get a list of all templates
        $list = scandir(APP_PATH . DS . 'templates');
        foreach($list as $file)
        {
            if($file[0] == "." || $file == "index.html") continue;
            if(!in_array($file, $aa))
            {
                $xml = APP_PATH . DS . 'templates' . DS . $file . DS .'template.xml';
                if(file_exists($xml))
                {
                    $xml = simplexml_load_file($xml);
                    $insert = array(
                        'name' => $file,
                        'type' => 'site',
                        'author' => $xml->info->author,
                        'status' => 0
                    );
                    $this->DB->insert('pcms_templates', $insert);
                }
            }
        }
        
        $this->load->view('templates', $data);
    }
    
/*
| ---------------------------------------------------------------
| P12: Console
| ---------------------------------------------------------------
|
*/    
    public function console()
    {
        // Make sure the user can view this page
        if( !$this->check_access('send_console_commands')) return;

        $realms = get_installed_realms();
        $selector = "<select id=\"realm\" name=\"realm\">\n";
        if( !empty($realms) )
        {
            foreach($realms as $realm)
            {
                $selector .= "\t<option value='". $realm['id'] ."'>". $realm['name'] ."</option>\n";
            }
        }
        else
        {
            $selector .= "\t<option value='0'>No Realms Installed</option>\n";
        }
        $selector .= "</select>\n";

        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Console",
            'page_desc' => "This page allows you to use a PHP / Ajax Command line console in which you can send commands to your server, or realms.",
            'realm_selector' => $selector
        );
        $this->load->view('console', $data);
    }

/*
| ---------------------------------------------------------------
| P13: Update
| ---------------------------------------------------------------
|
*/     
    public function update()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Remote Updater",
            'page_desc' => "This script allows you to update your CMS with just a click of a button.",
        );
        
        // Make sure the Openssl extension is loaded
        if(!extension_loaded('openssl'))
        {
            $message = 'Openssl extension not found. Please enable the openssl extension in your php.ini file (extension=php_openssl.dll).'.
                'You must enable openssl before using the remote updater';
            output_message('warning', $message);
            $this->load->view('blank', $data);
            return;
        }
        
        // Check for https support
        if(!in_array('https', stream_get_wrappers()))
        {
            output_message('warning', 'Unable to find the stream wrapper "https" - did you forget to enable it when you configured PHP?');
            $this->load->view('blank', $data);
            return;
        }
        
        // Make sure the client server allows fopen of urls
        if(ini_get('allow_url_fopen') == 1)
        {
            // Get the file changes from github
            $start = microtime(1);
            load_class('Debug')->silent_mode(true);
            $page = file_get_contents('https://api.github.com/repos/Plexis/Plexis/commits?per_page=30', false);
            load_class('Debug')->silent_mode(false);
            $stop = microtime(1);
            
            // Granted we have page contents
            if($page)
            {
                // Decode the results
                $commits = json_decode($page, TRUE);
                
                // Defaults
                $count = 0;
                $latest = 0;
                
                // Get the latest build
                $message = $commits[0]['commit']['message'];
                if(preg_match('/([0-9]+)/', $message, $latest))
                {
                    $latest = $latest[0];
                    if(CMS_BUILD < $latest)
                    {
                        $count = ($latest - CMS_BUILD);
                        if($count > 29)
                        {
                            output_message('warning', 'Your cms is out of date by more than 30 updates. You will need to manually update.');
                            $this->load->view('blank', $data);
                            return;
                        }
                    }
                }
                else
                {
                    output_message('warning', 'Unable to determine latest build');
                    $this->load->view('blank', $data);
                    return;
                }

                // Simple
                ($count == 0) ? $next = $commits[0] : $next = $commits[$count-1];
                $d = new DateTime($next['commit']['author']['date']);
                $date = $d->format("M j, Y - g:i a");

                
                // Build our page data
                $data['time'] = round($stop - $start, 5);
                $data['count'] = $count;
                $data['latest'] = $latest;
                $data['message'] = preg_replace('/([\[0-9\]]+)/', '', $next['commit']['message'], 1);
                $data['date'] = $date;
                $data['author'] = ucfirst($next['author']['login']);
                $data['sha'] = $next['sha'];
                $data['json_data_url'] = $next['url'];
                $data['more_info'] = "https://github.com/Plexis/Plexis/commit/". $next['sha'];
                $data['CMS_BUILD'] = CMS_BUILD;
                unset($commits);
                
                // No updates has a different view
                if($count == 0)
                {
                    $this->load->view('no_updates', $data);
                }
                else
                {
                    $this->load->view('updates', $data);
                }
            }
            else
            {
                output_message('warning', 'Unable to fetch updates from Github.');
                $this->load->view('blank', $data);
                return;
            }
        }
        else
        {
            output_message('warning', 'allow_url_fopen is not enabled in the php.ini file. Unable to continue.');
            $this->load->view('blank', $data);
            return;
        }
    }

/*
| ---------------------------------------------------------------
| P14: Logs
| ---------------------------------------------------------------
|
*/    
    public function logs()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "View Logs",
            'page_desc' => "Here you can view debug and error logs generated by the cms.",
        );
        $this->load->view('logs', $data);
    }
    
/*
| ---------------------------------------------------------------
| P15: Characters
| ---------------------------------------------------------------
|
*/ 
    public function characters($realmid = 0, $character = 0)
    {
        // Set new realm
        if( $realmid != 0 && realm_installed($realmid) )
        {
            $current = $realmid;
        }
        else
        {
            $current = get_realm_cookie();
        }

        // Get our installed realms
        $realms = get_installed_realms();
        
        // If no realms installed, display a message instead
        if(empty($realms))
        {
            // Build our page title / desc, then load the view
            $data = array(
                'page_title' => "Character Editor",
                'page_desc' => "This page allows you to edit character information, and preform actions such as transfering to another realm.",
            );
            $this->load->view('no_realms', $data);
            return;
        }
        
        // Editing a character?
        if($character != 0)
        {
            return;
        }
        
        // Otherwise, list
        $array = array();
        $set = false;
        foreach($realms as $realm)
        {
            $selected = '';
            if($realm['id'] == $current)
            {
                $selected = 'selected="selected" ';
            }
            
            // Add the language folder to the array
            $array[] = '<option value="'.$realm['id'].'" '. $selected .'>'.$realm['name'].'</option>';
        }
        
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Character Editor",
            'page_desc' => "This page allows you to edit character information, and preform actions such as transfering to another realm.",
            'realms' => $array
        );
        $this->load->view('characters', $data);
    }
    
/*
| ---------------------------------------------------------------
| P16: Statistics
| ---------------------------------------------------------------
|
*/ 
    public function statistics()
    {
        // Add visualize
        $this->Template->add_script( BASE_URL . '/application/admin/js/libs/jquery.visualize.js' );
        
        // Array of months
        $months = array('January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        
        // Get current month and year
        $date = date('n-Y');
        list($data['month'], $data['year']) = explode('-', $date);
        $month = $data['month'] - 1;
        $results = array();
        
        // Start back 5 months, and get stats for that month
        for($i = 5; $i >= 0; $i--)
        {
            // Establich this month / year and next
            $m = $month - $i;
            $y = $data['year'];
            $nm = $m + 1;
            $ny = $y;
            
            // If month is negative, add 12 months and subtract a year
            if($m < 0)
            {
                $m = $m + 12;
                --$y;
            }
            
            // Next month is 13? add a year and make the month january
            if($nm == 13)
            {
                $nm = 1;
                ++$ny;
            }
            
            // Get our registered stats for this month
            $query = "SELECT COUNT(id) AS `count` FROM `pcms_accounts` WHERE `registered` BETWEEN '$y-$m-00 00:00:00' AND '$ny-$nm-00 00:00:00'";
            $array = $this->DB->query( $query )->fetch_row();
            $results[] = array('name' => $months[$m], 'value' => $array['count']);
        }
        
        // Active in the last 24
        $time = date("Y-m-d H:i:s", time() - 86400 );
        $query = "SELECT COUNT(*) FROM `pcms_accounts` WHERE `last_seen` BETWEEN '$time' AND NOW()";
        $active = $this->DB->query( $query )->fetch_column();

        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Statistics",
            'page_desc' => "This page allows you to see various statistics about page views and user accounts.",
            'months' => $results,
            'account_count' => $this->realm->get_account_count(),
            'accounts_banned' => $this->realm->get_banned_count(),
            'inactive_accounts' => $this->realm->get_inactive_account_count(),
            'active_accounts' => $this->realm->get_active_account_count(),
            'accounts_active' => $active,
            'hits' => $this->Statistics->get_hits()
        );
        $this->load->view('stats', $data);
    }
 
/*
| ---------------------------------------------------------------
| UNFINISHED PAGES
| ---------------------------------------------------------------
|
*/
    
    
    function shop()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Shop Managment",
            'page_desc' => "This page llows you to manage your shop settings and packages for players to spend thier Web Points on.",
        );
        $this->load->view('under_construction', $data);
    }
    
    function donate()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Donation Managment",
            'page_desc' => "This page allows you to see all the donations your server has earned, as well as create and edit donation packages for users to buy.",
        );
        $this->load->view('under_construction', $data);
    }
    
    function support()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Support Settings",
            'page_desc' => "Here you can manage the support page as well as the FAQ's",
        );
        $this->load->view('under_construction', $data);
    } 

/*
| ---------------------------------------------------------------
| METHODS
| ---------------------------------------------------------------
*/
    
/*
| ---------------------------------------------------------------
| Method: check_access
| ---------------------------------------------------------------
|
| Displays a 403 if the user doesnt have access to this page
| @Param: (Bool) $s403 - Show 403?
|
*/ 
    protected function check_access($perm, $s403 = TRUE)
    {
        if( !$this->Auth->has_permission($perm))
        {
            if($s403) $this->show_403();
            return FALSE;
        }
        return TRUE;
    }
    
/*
| ---------------------------------------------------------------
| 403 Page
| ---------------------------------------------------------------
|
*/ 
    protected function show_403()
    {
        // Set our page title and desc
        $data['page_title'] = "Access Denied";
        $data['page_desc'] = "Your account does not have sufficient rights to view this page.";
        
        // Load the page, and we are done :)
        $this->load->view('blank', $data);
    }
}
// EOF