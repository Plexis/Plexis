<?php
class Admin extends Application\Core\Controller 
{
    public function __construct()
    {
        // Build the Core Controller
        parent::__construct();
        
        // Init a session var
        $this->user = $this->Session->get('user');
        
        // Make sure the user has admin access'
        if( !($this->user['is_admin'] == 1 || $this->user['is_super_admin'] == 1) )
        {
            redirect( SITE_URL );
            die();
        }
    }

/*
| ---------------------------------------------------------------
| Dashboard
| ---------------------------------------------------------------
|
*/
    public function index() 
    {
        // Get our PHP and DB versions
        $this->load->database( 'DB' );
        $info = $this->DB->server_info();
        $data['driver'] = ucfirst( $info['driver'] );
        $data['php_version'] = phpversion();
        $data['database_version'] = $info['version'];
        
        // Set our page title and desc
        $data['page_title'] = "Dashboard";
        $data['page_desc'] = "Here you have a quick overview of some features";
        
        // Load the page, and we are done :)
        $this->load->view('dashboard', $data);
    }

/*
| ---------------------------------------------------------------
| PHPinfo Page
| ---------------------------------------------------------------
|
*/ 
    public function phpinfo($html = FALSE) 
    {
        if($html == 'html')
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
| News Managment Page
| ---------------------------------------------------------------
|
*/ 
    public function news($action = NULL, $id = NULL)
    {
        // Load the News Model
        $this->load->model('News_Model');
  
        // No action? then load index screen
        if($action == NULL)
        {
            // Build our page variable data
            $data = array(
                'page_title' => "Manage News",
                'page_desc' => "From here, you can Edit, Delete, or create a new news post."
            );
            
            // Load the view
            $this->load->view('news_index', $data);
        }
        else
        {
            // Get our post
            $post = $this->News_Model->get_news_post($id);
            $post['body'] = stripslashes($post['body']);
            
            // Build our page variable data
            $data = array(
                'page_title' => "Edit News",
                'page_desc' => "Editing news post.",
                'id' => $post['id'],
                'title' => $post['title'],
                'body' => $post['body']
            );
            
            // Load the view
            $this->load->view('news_edit', $data); 
        }
    }

/*
| ---------------------------------------------------------------
| Manage Users
| ---------------------------------------------------------------
|
*/    
    public function users($username = NULL)
    {
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
            // Load the database
            $this->load->database( 'DB' );
            
            // Get users information. We can use GET because the queries second param will be cleaned
            // by the PDO class when bound to the "?".
            $query = "SELECT * FROM `pcms_accounts` INNER JOIN `pcms_account_groups` ON 
                pcms_accounts.group_id = pcms_account_groups.group_id WHERE `username` LIKE ?";
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
                // Make sure we have our realm loaded
                (!isset($this->realm)) ? $this->load->realm() : '';
                
                // Use the realm database to grab user information first
                $user2 = $this->realm->fetch_account($user['id']);
                
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
| Site Settings
| ---------------------------------------------------------------
|
*/    
    public function settings()
    {
        // Load our config class
        $Config = load_class('Config');
        
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Site Settings",
            'page_desc' => "Here you can manage the account of all your users.",
            'config' => $Config->get_all('app')
        );
        $this->load->view('site_settings', $data);
    }

/*
| ---------------------------------------------------------------
| Database Operations and Config
| ---------------------------------------------------------------
|
*/     
    function database()
    {
        // Load our config class
        $Config = load_class('Config');

        // Make sure user is super admin for ajax
        if($this->user['is_super_admin'] != 1)
        {
            // Build our page title / desc, then load the view
            $data = array(
                'page_title' => "Access Denied",
                'page_desc' => "Insufficient Rights"
            );
            output_message('error', 'access_denied_privlages');
            $this->load->view('blank', $data);
        }
        else
        {
            // Build our page title / desc, then load the view
            $data = array(
                'page_title' => "Database Config & Operations",
                'page_desc' => "On this page, you can adjust your database configuration options, Backup, and optimize your tables. Only users with the \"Super Admin\" privliage can access this page.",
                'config' => $Config->get_all('DB')
            );
            $this->load->view('database', $data);
        }
    }

/*
| ---------------------------------------------------------------
| Registration Settigns
| ---------------------------------------------------------------
|
*/    
    function registration()
    {
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
| Realm Managment
| ---------------------------------------------------------------
|
*/    
    function realms($subpage = 'index', $id = NULL)
    {
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
                // Load installed drivers
                $drivers = get_wowlib_drivers();
                if($drivers == FALSE) $drivers = array();
                
                // Build our page title / desc, then load the view
                $data = array(
                    'page_title' => "Edit Realm",
                    'page_desc' => "Here you can change the DB settings for your realm, as well as the driver.",
                    'drivers' => $drivers
                );
                $this->load->view('realms_edit', $data);
                break;
            
            // INSTALL
            case "install":
                if($id === NULL || !is_numeric($id)) redirect('admin/realms');
                
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
                
                // Load installed drivers
                $drivers = get_wowlib_drivers();
                if($drivers == FALSE) $drivers = array();
                
                // Build our page title / desc, then load the view
                $data = array(
                    'page_title' => "Realm Installation",
                    'page_desc' => "On this page you will be able to install a new realm for use on the site. Installing a realm allows you as well as users to 
                        see statistics about the realm, view online characters, and user character tools such as Character Rename.",
                    'realm' => $realm,
                    'drivers' => $drivers
                );
                $this->load->view('realms_install', $data);
                break;
                
            default:
                redirect('admin/realms');
                break;
        }
    }
 
/*
| ---------------------------------------------------------------
| UNFINISHED PAGES
| ---------------------------------------------------------------
|
*/
    
    function characters()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Character Editor",
            'page_desc' => "This page allows you to edit character information, and preform actions such as transfering to another realm.",
        );
        $this->load->view('under_construction', $data);
    }
    
    function modules()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Module Managment",
            'page_desc' => "On this page, you can install and manage your installed modules. You may also edit module config files here.",
        );
        $this->load->view('under_construction', $data);
    }
    
    function vote()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Vote System Settings",
            'page_desc' => "Here you can setup your vote sites for users to vote on, and Create / Edit / Delete existing vote links.",
        );
        $this->load->view('under_construction', $data);
    }
    
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
    
    function statistics()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Statistics",
            'page_desc' => "This page allows you to see various statistics about page views and user accounts.",
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
    
    function templates()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Template Manager",
            'page_desc' => "This page allows you to manage your templates, which includes uploading, installation, and un-installation.",
        );
        $this->load->view('under_construction', $data);
    }
    
    function console()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Console",
            'page_desc' => "This page allows you to use a PHP / Ajax Command line console in which you can send commands to your server, or realms.",
        );
        $this->load->view('under_construction', $data);
    }
    
    function update()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "Remote Updater",
            'page_desc' => "This script allows you to update your CMS with just a click of a button.",
        );
        $this->load->view('update', $data);
    }
    
    function logs()
    {
        // Build our page title / desc, then load the view
        $data = array(
            'page_title' => "View Logs",
            'page_desc' => "Here you can view debug and error logs generated by the cms.",
        );
        $this->load->view('under_construction', $data);
    }
}
// EOF