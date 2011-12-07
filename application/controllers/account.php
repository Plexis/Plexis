<?php
class Account extends Application\Core\Controller 
{

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
| Index Page
| ---------------------------------------------------------------
|
*/
    public function index() 
    {
        // Redirect to the login page if the user is not logged in
        if($this->user['logged_in'] == FALSE)
        {
            redirect('account/login');
        }
        
        // Load the page, and we are done :)
        $this->load->view('index');
    }

/*
| ---------------------------------------------------------------
| Login Page
| ---------------------------------------------------------------
|
*/
    public function login() 
    {
        // If the user is logged in, we dint need to be here
        if($this->user['logged_in'] == TRUE)
        {
            redirect('account');
        }

        // Do we have login information?
        if(isset($_POST['username']))
        {
            // Load the Form Validation script
            $this->load->library('validation');
            
            // Tell the validator that the username and password must NOT be empty
            $this->validation->set( array(
                'username' => 'required|pattern[(^[A-Za-z0-9_-]{3,24}$)]', 
                'password' => 'required|min[3]|max[24]') 
            );
            
            // If both the username and password pass validation
            if( $this->validation->validate() == TRUE )
            {
                // Load the input cleaner
                $this->input = load_class('Input');
        
                // Use the XSS filter on these!
                $username = $this->input->post('username', TRUE);
                $password = $this->input->post('password', TRUE);
                
                if($this->Auth->login($username, $password))
                {
                    // Success, redirect in 4 secs and load the success message
                    redirect('account', 3);
                    $this->load->view('login_success');
                }
                else
                {
                    // Failed to validate login information, load the login page again
                    $this->load->view('login');
                }
            }
            else
            {
                // Failed to validate fields, load the login page again, spit an error message
                output_message('error', 'login_failed_field_invalid');
                $this->load->view('login');
            }
        }
        
        // Just load the login page
        else
        {
            $this->load->view('login');
        }
    }

/*
| ---------------------------------------------------------------
| Logout Page
| ---------------------------------------------------------------
|
*/
    public function logout() 
    {
        // Redirect to the login page if the user is not logged in
        if($this->user['logged_in'] == FALSE)
        {
            redirect('account/login');
        }
        
        // Destroy the users session
        $this->Auth->logout();
        
        // Load the page, and we are done :)
        $this->load->view('logout');
    }

/*
| ---------------------------------------------------------------
| Register Page
| ---------------------------------------------------------------
|
*/
    public function register() 
    {
        // Redirect to the users dashboard if already logged in
        if($this->user['logged_in'] == TRUE)
        {
            redirect('account');
        }
        
        // Make sure the config says we can register
        if( config('allow_registration') == FALSE )
        {
            output_message('error', 'reg_disabled');
            $this->load->view('blank');
            return;
        }
        
        // Load the input cleaner
        $this->Input = load_class('Input');
        
        // See if the admin requires a registration key, and IF there is one
        if( config('reg_registration_key') == TRUE )
        {
            // Check for a key
            $key = $this->Input->cookie('reg_key', TRUE);
            if( $key == FALSE )
            {
                // Check if the user recently posted the key
                if( isset($_POST['key']) )
                {
                    // If key is posted, If so we must validate it
                    $result = $this->DB->query("SELECT * FROM `pcms_reg_keys` WHERE `key`=?", array($_POST['key']))->fetch_row();
                    if($result == FALSE)
                    {
                        // Key form
                        output_message('error', 'reg_failed_invalid_key');
                        $this->load->view('registration_key');
                        return;
                    }
                    else
                    {
                        // Give the user 1 hour to register, otherwise he must re-enter the reg key
                        $this->Input->set_cookie('reg_key', $result['key'], time() + 3600);
                        $this->load->view('register');
                        return;
                    }
                }
                else
                {
                    // No posted info, load the Key form
                    $this->load->view('registration_key');
                    return;
                }
            }
            else
            {
                // Process if key is valid
                $result = $this->DB->query("SELECT * FROM `pcms_reg_keys` WHERE `key`=?", array($key))->fetch_row();
                if($result == FALSE)
                {
                    // Reset the Registration key and start over... load the Key form
                    $this->Input->set_cookie('reg_key', $key, (time() -1));
                    output_message('error', 'reg_failed_invalid_key');
                    $this->load->view('registration_key');
                    return;
                }
                else
                {
                    // Key is valid, lets go!
                    goto Posted;
                }
            }
        }
        
        // Registrer keys disabled
        else
        {
            Posted:
            {
                // Process if we have POST information
                if( isset($_POST['action']) && $_POST['action'] == "register" )
                {
                    goto Process;
                }
                else
                {
                    $this->load->view('register');
                    return;
                }
            }
        }
        
        // Our main registration processing station
        Process:
        {
            // Load the Form Validation script
            $this->load->library('validation');
            
            // Tell the validator that the username and password must NOT be empty, as well
            // as match a pattern. Same goes for the email field.
            $this->validation->set( array(
                'username' => 'required|pattern[(^[A-Za-z0-9_-]{3,24}$)]', 
                'password' => 'required|min[3]|max[24]',
                'email' => 'required|email') 
            );
            
            // If everything passes validation, we are good to go
            if( $this->validation->validate() == TRUE )
            {
                // Use the XSS filter on these!
                $username = $this->Input->post('username', TRUE);
                $password = $this->Input->post('password', TRUE);
                $email = $this->Input->post('email', TRUE);
                
                // Use the AUTH class to register the user officially
                if( $this->Auth->register($username, $password, $email) == TRUE )
                {
                    // Remove registration key IF enabled
                    if( config('reg_registration_key') == TRUE )
                    {
                        $this->Input->set_cookie('reg_key', $key, (time() -1));
                        $this->DB->delete('pcms_reg_keys', "`key`='".$key."'");
                    }
                    
                    // Log the user in, and redirect
                    $this->Auth->login($username, $password);
                    output_message('success', 'reg_success');
                    $this->load->view('register_success', array('username' => $username) );
                }
                else
                {
                    // Message will already be there, no need to make one
                    $this->load->view('register');
                }
            }
            else
            {
                output_message('error', 'reg_failed_field_invalid');
                $this->load->view('register');
            }
        }
    }
}
// EOF