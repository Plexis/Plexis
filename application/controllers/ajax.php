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
                // Dont close this script!
                ignore_user_abort(true);
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
        $output = $this->wowlib->characters->get_online_list_datatables(true);
        
        // Loop, and add options
        foreach($output['aaData'] as $key => $value)
        {
            $g = $value[5];
            $r = $value[3];
            $race = $this->wowlib->characters->race_to_text($r);
            $class = $this->wowlib->characters->class_to_text($value[4]);
            $zone = $this->wowlib->zone->name($value[6]);
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
        // Make sure we arent getting direct access
        $this->check_permission('account_access');
        
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
                'type' => $type,
                'data' => $message
            )
        );
    }
}