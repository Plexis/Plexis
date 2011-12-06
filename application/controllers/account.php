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
        
        // Do we have posted information?
        if(isset($_POST['username']))
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
                // Load the input cleaner
                $this->input = load_class('Input');
        
                // Use the XSS filter on these!
                $username = $this->input->post('username', TRUE);
                $password = $this->input->post('password', TRUE);
                $email = $this->input->post('email', TRUE);
                
                // Use the AUTH class to register the user officially
                if( $this->Auth->register($username, $password, $email) == TRUE )
                {
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
        
        // else, just Load the default page
        else
        {
            $this->load->view('register');
        }
    }
}
// EOF