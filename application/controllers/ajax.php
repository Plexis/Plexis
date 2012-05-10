<?php

class Ajax extends Application\Core\Controller
{

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
        
        // Load the input class for xss filtering
        $this->input = load_class('Input');
        
        // Make sure user is an admin!
        if( !$this->Auth->has_permission('admin_access') )
            die('Admin Access Only!');
    }
    
/*
| ---------------------------------------------------------------
| A01: realms()
| ---------------------------------------------------------------
|
| This method is used to update / install realms via Ajax
*/    
    public function realms()
    {
        // Load the ajax model and action
        $this->load->model('Ajax_Model', 'model');
        $action = $this->input->post('action');

        // Find out what we are doing here by our requested action
        switch($action)
        {
            case "status":
                $this->model->realm_status();
                break;
            
            default:
                $this->output(false, "Unknown Action");
                break;
        }
    }
    
/*
| ---------------------------------------------------------------
| A02: onlinechars()
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
        $output = $this->wowlib->get_online_list_datatables(true);
        
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
| A03: vote()
| ---------------------------------------------------------------
|
| This method is used to process votes
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
            $this->output(false, 'No post action!');
        }
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
        if(isset($_POST['action']))
        {
            // Get our action
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
                    // Load the commit.info file
                    $sha = $type = trim( $this->input->post('sha') );
                    $url = 'https://raw.github.com/Plexis/Plexis/'. $sha .'/commit.info';
                
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
                    $contents = file_get_contents($url, false);
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
                                    $success = FALSE;
                                    $text = 'Error creating directory "'. $dirname .'"';
                                    goto Output;
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
                            
                        case "D":
                            $removed = TRUE;
                            $Fs->delete($filename);
                            break;
                    } // End witch type
                    
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
        else
        {
            echo json_encode( array('success' => false, 'message' => "no action") );
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
        if(!is_array($message))
        {
            $lang = load_language_file( 'messages' );
            $message = (isset($lang[$message])) ? $lang[$message] : $message;
        }
        
        // Build our Json return
        $return = array();
        
        if($success == TRUE)
        {
            // Remove error tag on success, but allow warnings
            if($type == 'error') $type = 'success';

        }
        
        // Output to the browser in Json format
        echo json_encode(
            array(
                'success' => $success,
                'type' => $type,
                'data' => $message
            )
        );
    }
}