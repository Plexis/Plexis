<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author: 		Steven Wilson
| Copyright:	Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
|---------------------------------------------------------------
|
| Navigation. (user CTRL + f to move quickly)
|---------------------------------------------------------------
| P01 - Index Page
| P02 - Login Page
| P03 - Logout Page
| P04 - Register Page
| P05 - Account Activation Page
| P06 - Account Recovery Page
| P07 - Update (password / email) Page
| P08 - Captcha Page
|
*/
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
        
        // Make the user info a little easier to get
        $this->user = $this->Session->get('user');
    }

/*
| ---------------------------------------------------------------
| P01: Index Page
| ---------------------------------------------------------------
|
*/
    public function index() 
    {
        // Redirect to the login page if the user is not logged in
        if($this->user['logged_in'] == FALSE)
        {
            redirect('account/login');
            die();
        }
        
        // If the users account revovery QA isnt set, they need to... NOW!
        if($this->user['_account_recovery'] == FALSE)
        {
            redirect('account/recover/set');
            return;
        }
        
        // Fetch account data from the realm
        $data = $this->realm->fetch_account($this->user['id']);
        
        // Load the page, and we are done :)
        $this->load->view('index', $data);
    }

/*
| ---------------------------------------------------------------
| P02: Login Page
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
                    // Success
                    (isset($_SERVER['HTTP_REFERER'])) ? redirect( $_SERVER['HTTP_REFERER'] ) : redirect('account');
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
| P03: Logout Page
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
        (isset($_SERVER['HTTP_REFERER'])) ? redirect( $_SERVER['HTTP_REFERER'] ) : redirect( SITE_URL );
    }

/*
| ---------------------------------------------------------------
| P04: Register Page
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
        
        // Do our captcha check
        $enable_captcha = config('enable_captcha');
        if( $enable_captcha == TRUE )
        {
            $Captcha = $this->load->library('Captcha');
            if( $Captcha->is_compatible() == FALSE )
            {
                config_set('enable_captcha', false);
                $enable_captcha = FALSE;
            }
            unset($Captcha);
        }
        
        // Load our secret questions
        $data['secret_questions'] = get_secret_questions();
        
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
                    if($result == FALSE || $result['usedby'] >= 0) //'usedby' will only not equal -1 if someone has already signed up with it, so we need to prevent further use of the key.
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
                        $this->load->view('register', $data);
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
                if($result == FALSE || $result['usedby'] >= 0) //'usedby' will only not equal -1 if someone has already signed up with it, so we need to prevent further use of the key.
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
                    $this->load->view('register', $data);
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
            ($enable_captcha == TRUE) ? $add = array('sa' => 'required|min[3]|max[24]') : $add = array();
            $this->validation->set( array(
                'username' => 'required|pattern[(^[A-Za-z0-9_-]{3,24}$)]', 
                'password1' => 'required|min[3]|max[24]',
                'password2' => 'required|min[3]|max[24]',
                'email' => 'required|email'
                ) + $add
            );
            
            // If everything passes validation, we are good to go
            if( $this->validation->validate() == TRUE )
            {
                // Check for captcha validation
                if( $enable_captcha == TRUE )
                {
                    $captcha = strtolower( $this->Input->post('captcha') );
                    if($captcha != strtolower($_SESSION['Captcha']))
                    {
                        output_message('error', 'captcha_incorrect');
                        $this->load->view('register', $data);
                        return;
                    }
                }
                
                // Use the XSS filter on these!
                $username = $this->Input->post('username', TRUE);
                $password = $this->Input->post('password1', TRUE);
                $password2 = $this->Input->post('password2', TRUE);
                $email = $this->Input->post('email', TRUE);
                $sq = $this->Input->post('sq');
                $sa = $this->Input->post('sa', TRUE);
                
                // Check that the 2 passwords matched
                if($password != $password2)
                {
                    output_message('error', 'passwords_dont_match');
                    $this->load->view('register');
                    return;
                }
                
                // Check if the email is already in use
                if( config('reg_unique_email') == TRUE )
                {
                    // Check the DB for the email address
                    $ee = $this->realm->email_exists($email);
                    if($ee == TRUE)
                    {
                        output_message('error', 'reg_failed_email_exists');
                        $this->load->view('register', $data);
                        return;
                    }
                }
                
                // Use the AUTH class to register the user officially
                $id = $this->Auth->register($username, $password, $email, $sq, $sa);
                if( $id == TRUE )
                {
                    // Remove registration key IF enabled
                    if( config('reg_registration_key') == TRUE )
                    {
                        $this->Input->set_cookie('reg_key', $key, (time() -1));
                        //$this->DB->delete('pcms_reg_keys', "`key`='".$key."'");
                        
                        // Set the 'usedby' field for the reg key.
                        $this->DB->update("pcms_reg_keys", array('usedby' => $id), "`key` = '$key'");
                    }
                    
                    // Check for email verification
                    if( config('reg_email_verification') == TRUE )
                    {
                        // Setup our variables and load our extensions
                        $site_title = config('site_title');
                        $site_email = config('site_email');
                        $lang = load_language_file('emails');
                        $this->load->library('email');
                        $this->load->model('Account_model', 'account');
                        
                        // generate our random account verification code
                        $genkey = $this->account->create_key($username);
                        $href = SITE_URL . "/account/activate/".$genkey;
                        $message = vsprintf( $lang['email_activate_message'], array( $username, $site_title, $href, $href ) );
                        
                        // Build the email
                        $this->email->to($email, $username);
                        $this->email->from( $site_email, $site_title );
                        $this->email->subject( $lang['email_activate_subject'] );
                        $this->email->message( $message );
                        $sent = $this->email->send();
                        
                        // Check if our email sent correctly
                        if($sent == TRUE)
                        {
                            output_message('success', 'reg_success_activation_required');
                            $this->load->view('blank');
                        }
                        else
                        {
                            output_message('warning', 'reg_success_email_error');
                            $this->load->view('blank');
                        }
                    }
                    else
                    {
                        // Log the user in, and redirect
                        $this->Auth->login($username, $password);
                        output_message('success', 'reg_success');
                        $this->load->view('register_success', array('username' => $username) );
                    }
                }
                else
                {
                    // Message will already be there, no need to make one
                    $this->load->view('register', $data);
                }
            }
            else
            {
                output_message('error', 'reg_failed_field_invalid');
                $this->load->view('register', $data);
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| P05: Account Activation Page
| ---------------------------------------------------------------
|
*/
    public function activate($key = FALSE) 
    {
        // Make sure we have a key
        if($key == FALSE || strlen( trim($key) ) != 20) goto Invalid;
        
        // Load the account model
        $this->load->model('Account_model', 'account');
        
        // Verify the key
        $username = $this->account->verify_key($key);
        if($username == FALSE) goto Invalid;
        
        // We have a valid key, now activate the account
        $result = $this->DB->update('pcms_accounts', array('activated' => 1), "`username`='".$username."'");
        if($result == TRUE)
        {
            // Unlock account
            $query = "SELECT `id` FROM `pcms_accounts` WHERE `username`=?";
            $result = $this->DB->query( $query, array($username) )->fetch_column();
            $this->realm->unlock_account($result);
            
            // Output a success message
            output_message('success', 'account_activate_success');
            $this->load->view('blank');
            return;
        }
        
        // Process an invalid key
        Invalid:
        {
            output_message('error', 'account_unable_to_activate');
            $this->load->view('blank');
            return;
        }
    }
    
/*
| ---------------------------------------------------------------
| P06: Account Recovery
| ---------------------------------------------------------------
|
*/
    public function recover($mode = NULL) 
    {
        // Check to see if we have a mode!
        if($mode != NULL)
        {
            switch($mode)
            {
                case "set":
                    // Shouldnt be here if we arent logged in!
                    if($this->user['logged_in'] == FALSE) goto Step1;
                    
                    // Make sure Users QA isnt set.. If it is then he shouldnt be here!
                    if($this->user['_account_recovery'] == TRUE) redirect('account');
                    
                    // Check for posted data
                    if(isset($_POST['action'])) goto Process;
                    
                    // Get our questions and load the page
                    $data['secret_questions'] = get_secret_questions();
                    $this->load->view('secret_questions', $data);
                    break;
                
                default:
                    if($this->user['logged_in'] == FALSE) goto Step1;
                    redirect('account');
            }
            return;
        }
        else
        {
            // If the user is logged in, we dont need to be here
            if($this->user['logged_in'] == TRUE)
            {
                redirect('account');
            }
        
            // Do we have login information?
            if(isset($_POST['action'])) goto Process;
        }
        
        
        // Recovery Form, Step 1
        Step1:
        {
            $this->load->view('recover');
            return;
        }
        
        // Recovery Form, Step 2
        Step2:
        {
            $data = array(
                'question' => $r_data['question'],
                'username' => $username
            );
            $this->load->view('recover_step2', $data );
            return;
        }
        
        // Our process post processing
        Process:
        {
            if(!isset($_POST['action'])) goto Step1;
            
            // Load the account Model
            $this->load->model('account_model', 'account');
            
            switch($_POST['action'])
            {
                case "set":
                    
                    // Load the input class and use the XSS filter on these!
                    $this->Input = load_class('Input');
                    $sq = $this->Input->post('question', TRUE);
                    $sa = $this->Input->post('answer', TRUE);
                    
                    // Fetch account data from the realm
                    $data = $this->realm->fetch_account($this->user['id']);

                    // Secret question / answer processing
                    if($sq != NULL && $sa != NULL)
                    {
                        // Set recovery data
                        $set = $this->account->set_recovery_data($data['username'], $sq, $sa);
                        
                        // Process the result
                        if($set == TRUE)
                        {
                            // Load the account dashboard, and we are done :)
                            output_message('success', 'account_recovery_set_success');
                            $this->load->view('index', $data);
                            return;
                        }
                        else
                        {
                            // No recovery data means we cant do anything here
                            output_message('error', 'account_recovery_set_failed');
                            $this->load->view('blank');
                            return;
                        }
                    }
                    else
                    {
                        // Back to step 1 because fields were not filled correctly
                        output_message('error', 'submit_failed_fields_empty');
                        goto Step1;
                    }
                    break;
                    
                case "recover":
                    // Get our current step
                    if( !isset($_POST['step']) ) goto Step1;

                    // Porcess our step
                    switch($_POST['step'])
                    {
                        case 1:
                            // Load the validation script and set our rules
                            $this->load->library('validation');
                            $this->validation->set( array(
                                'username' => 'required|pattern[(^[A-Za-z0-9_-]{3,24}$)]', 
                                'email' => 'required|email'
                                ) 
                            );
                            
                            // Check to make sure we pass validation
                            if($this->validation->validate() == TRUE)
                            {
                                // load the input class
                                $this->Input = load_class('Input');
                                $username = $this->Input->post('username', TRUE);
                                $email = $this->Input->post('email', TRUE);
                                
                                // Load recovery data
                                $r_data = $this->account->get_recovery_data($username);
                                if(!is_array($r_data))
                                {
                                    // If false, User doesnt exists, else recovery data not set
                                    if($r_data == FALSE)
                                    {
                                        output_message('error', 'username_doesnt_exist');
                                        goto Step1;
                                    }
                                    else
                                    {
                                        output_message('error', 'account_recover_failed_not_set');
                                        $this->load->view('blank');
                                    }
                                }
                                
                                // Make sure the emails match! Else, back to step 1
                                if($r_data['email'] != $email)
                                {
                                    output_message('error', 'account_recover_invalid_email');
                                    goto Step1;
                                }
                                
                                // Good to go to step 2
                                goto Step2;
                            }
                            else
                            {
                                // Form validation failed, back to step 1
                                output_message('error', 'form_validation_failed');
                                goto Step1;
                            }
                            break;

                        case 2:
                            // Make sure we have post data
                            if(!isset($_POST['answer'])) goto Step1;
                            
                            // load the input class
                            $this->Input = load_class('Input');
                            $username = $this->Input->post('username', TRUE);
                            $answer = $this->Input->post('answer', TRUE);
                            
                            // Load recovery data
                            $r_data = $this->account->get_recovery_data($username);
                            if(!is_array($r_data))
                            {
                                // If false, User doesnt exists, else recovery data not set
                                if($r_data == FALSE)
                                {
                                    output_message('error', 'username_doesnt_exist');
                                    goto Step1;
                                }
                                else
                                {
                                    output_message('error', 'account_recover_failed_not_set');
                                    $this->load->view('blank');
                                }
                            }

                            // Check that the secret answer was correct
                            if( trim( strtolower($answer) ) == trim( strtolower($r_data['answer']) ) )
                            {
                                // Load the account model as it holds the code to change the password etc
                                $result = $this->account->process_recovery($r_data['id'], $username, $r_data['email']);
                                
                                // The message will be there if we whether we failed or succeded
                                $this->load->view('blank');
                                return;
                                
                            }
                            else
                            {
                                // Answer was incorrect, so back to step 2
                                output_message('error', 'account_recover_failed_wrong_answer');
                                goto Step2;
                            }
                            break;
                            
                        default:
                            goto Step1;
                            break;
                        
                    }
                    break;
                    
                default:
                    goto Step1;
                    break;
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| P07: Account Update (password / email) Pages
| ---------------------------------------------------------------
|
*/    
    public function update($mode = NULL)
    {
        // Make sure we have a directive
        if($mode == NULL) redirect('/');
        
        // Make sure we are logged in
        if($this->user['logged_in'] == FALSE) redirect('/');
        
        // Check for POST data, if we have some skip to Process
        if(isset($_POST['action'])) goto Process;
        
        // Load our page based off our current mode
        switch($mode)
        {
            case "password":
                $this->load->view('change_password');
                return;
                
            case "email":
                $this->load->view('change_email');
                return;
                
            default:
                show_404();
        }
        
        // Main processing form
        Process:
        {
            // Load our validation libray, and process out current action
            $this->load->library('validation');
            $action = $_POST['action'];
            switch($action)
            {
                case "change-password":
                    // Set our validation rules
                    $this->validation->set( array(
                        'password1' => 'required|min[3]|max[24]',
                        'password2' => 'required|min[3]|max[24]',
                        'old_password' => 'required'
                    ));
                    
                    // validate our post data was filled out correctly
                    if($this->validation->validate() == TRUE)
                    {
                        // Load the Input class and run the XSS filter
                        $this->Input = load_class('Input');
                        $password = $this->Input->post('password1', TRUE);
                        $password2 = $this->Input->post('password2', TRUE);
                        $oldpass = $this->Input->post('old_password', TRUE);
                        
                        // Make sure the new passwords match
                        if($password != $password2)
                        {
                            output_message('error', 'passwords_dont_match');
                            $this->load->view('change_password');
                            return;
                        }
                        
                        // Tell the realm to validate the provided password
                        $valid = $this->realm->validate_login($this->user['username'], $oldpass);
                        if(!$valid)
                        {
                            output_message('error', 'account_update_login_failed');
                            $this->load->view('change_password');
                            return;
                        }
                        
                        // we are good, change the password
                        $success = $this->realm->change_password($this->user['id'], $password);
                        
                        if($success == TRUE)
                        {
                            output_message('success', 'account_update_pass_success');
                            $this->load->view('blank');
                            return;
                        }
                        
                        // Else we failed :(
                        output_message('error', 'account_update_pass_failed');
                        $this->load->view('blank');
                        return;
                    }
                    else
                    {
                        // Form failed to validate :/
                        output_message('error', 'form_validation_failed');
                        $this->load->view('change_password');
                        return;
                    }
                    break;
                    
                case "change-email":
                    // Set our validation rules
                    $this->validation->set( array(
                        'password' => 'required|min[3]',
                        'old_email' => 'required|email',
                        'new_email' => 'required|email'
                    ));
                    
                    // validate our post data was filled out correctly
                    if($this->validation->validate() == TRUE)
                    {
                        // Load the Input class and run the XSS filter
                        $this->Input = load_class('Input');
                        $password = $this->Input->post('password', TRUE);
                        $old = $this->Input->post('old_email', TRUE);
                        $new = $this->Input->post('new_email', TRUE);

                        // Tell the realm to validate the provided password
                        $valid = $this->realm->validate_login($this->user['username'], $password);
                        if(!$valid)
                        {
                            output_message('error', 'account_update_login_failed');
                            $this->load->view('change_email');
                            return;
                        }
                        
                        // If the email didnt change, then say so
                        if($old == $new)
                        {
                            output_message('warning', 'account_update_nochanges');
                            $this->load->view('change_email');
                            return;
                        }
                        
                        // If an email wasnt set, the just set the damn thing
                        $result = $this->DB->query('SELECT `email` FROM `pcms_accounts` WHERE `id`=?', array($this->user['id']))->fetch_column();
                        if($result == NULL)
                        {
                            goto SetEmail;
                        }
                        
                        // Verify the email addresses are the same
                        if($old != $result)
                        {
                            output_message('error', 'account_update_email_invalid');
                            $this->load->view('change_email');
                            return;
                        }
                        
                        // Our set email process
                        SetEmail:
                        {
                            // Update the realm database with the new email
                            $r = $this->realm->change_email($this->user['id'], $new);
                            if($r == FALSE)
                            {
                                output_message('error', 'account_update_email_failed');
                                $this->load->view('blank');
                                return; 
                            }
                            
                            // Now update the cms accounts table with the new email
                            $r = $this->DB->update('pcms_accounts', array('email' => $new), "`id`=".$this->user['id']);
                            if($r == FALSE)
                            {
                                output_message('error', 'account_update_email_failed');
                                $this->load->view('blank');
                                return; 
                            }
                            
                            // If we are here, we have a success!
                            output_message('success', 'account_update_email_success');
                            $this->load->view('blank');
                            return; 
                        }

                    }
                    else
                    {
                        // For failed to validate
                        output_message('error', 'form_validation_failed');
                        $this->load->view('change_email');
                        return;
                    }
                    break;
                    
                default:
                    // By default, just reload the page
                    $this->load->view('change_email');
                    return;
            }
        }
    }

/*
| ---------------------------------------------------------------
| P08: Captcha Image Page
| ---------------------------------------------------------------
|
*/    
    public function captcha()
    {
        // Load the captcha Library
        $this->load->library('Captcha');

        // Set our content type to an image
        header("Content-type: image/png");

        // Output the image
        $this->Captcha->display(6, 25, 75, NULL, FALSE, TRUE, TRUE);

        // Store the Captcha string into an array for verification later on.
        $_SESSION['Captcha'] = $this->Captcha->get_string();
    }
    
/*
| ---------------------------------------------------------------
| P09: Vote
| ---------------------------------------------------------------
|
*/    
    public function vote($action = NULL, $id = 0)
    {
        // Make sure the user is logged in HERE!
        if($this->user['logged_in'] == FALSE) redirect('account/login');
        
        // Load the vote model, and time helper
        $this->load->model('Vote_Model', 'model');
        $this->load->helper('Time');
        
        // See if we need redirecting!
        if($action == 'out' && $id != 0)
        {
            $site = $this->model->get_vote_site($id);
            redirect( $site['votelink'] );
            die();
        }
        
        // Load this users vote data
        $vote_data = $this->model->get_data( $this->user['id'] );
        
        // Get all the vote sites information
        $list = $this->model->get_vote_sites();
        $sites = array();
        
        // Correct the array keys
        foreach($list as $site)
        {
            $sites[ $site['id'] ] = $site;
        }
        
        // Process the time left for each site
        $time = time();
        foreach($vote_data as $key => $value)
        {
            // Get our remaining time left
            $left = $value - $time;
            if($left > 0)
            {
                // Time left still, Let make a fancy time string!
                $sites[$key]['disabled'] = 'disabled="disabled"';
                $sites[$key]['time_left'] = sec2hms($left);
            }
            else
            {
                // expired time, Good to vote again
                $sites[$key]['disabled'] = '';
                $sites[$key]['time_left'] = "N/A";
            }
        }
        
        // Prepare for view
        $data['sites'] = $sites;
        $this->load->view('vote', $data);
    }
    
/*
| ---------------------------------------------------------------
| P10: Invitation Keys
| ---------------------------------------------------------------
|
*/
    public function invite_keys($mode = NULL, $key_id = NULL)
    {
        $can_create_keys = config("reg_user_key_creation");
        $data["can_create_keys"] = $can_create_keys;
        
        // Only process the mode IF we allow users to create keys!
        if( $can_create_keys == 1)
        {
            // Process key creation first.
            if( $mode == "create" )
            {
                // Let's generate the key.
                $new_key = "";
                
                // Start by getting the username, all lowercase, alphanumeric only.
                $username = strtolower( $this->user['username'] );
                $username = preg_replace("/[^a-z0-9]/i", "", $username);
                
                // Get a string containing the current IP address represented as a long integer
                // and the current Unix timestamp with microsecond prescision.
                $longid = sprintf("%u%d", ip2long($_SERVER['REMOTE_ADDR']), microtime(true));
                
                //Each invitation key consists of a SHA1 hash of the above 'longid' prepended with the user's name.
                $new_key = $username . sha1($longid);
                
                $key_query_data = array(
                    "key" => $new_key, 
                    "sponser" => $this->user['id']
                );
                
                // Insert it into the pcms_reg_keys table.
                $this->DB->insert("pcms_reg_keys", $key_query_data);
                
                // Redirect back to /account/invite_keys/ so that F5 won't resubmit the form.
                redirect("account/invite_keys");
            }
            
            // Process key deletion next.
            if( $mode == "delete" && $key_id !== NULL )
            {
                // Prevent possible SQLi vulnerability, we only continue processing if $key_id is strictly alpanumeric and between 3 and 128 characters long (inclusive).
                if( is_numeric($key_id) )
                {
                    $query = "SELECT * FROM `pcms_reg_keys` WHERE `id` = ?";
                    $key_query = $this->DB->query($query, array($key_id))->fetch_row(); //Get the key.
                    
                    // Check to make sure the query didn't fail.
                    if( $key_query != FALSE )
                    {
                        $sponsor = $key_query["sponser"];
                        
                        // Only allow the key to be deleted if it belongs to the currently logged in user.
                        if( $sponsor == $this->user["id"] ) $this->DB->delete("pcms_reg_keys", "`id` = '$key_id'");
                    }
                }
                
                // Redirect back to /account/invite_keys/ so that F5 won't resubmit the form.
                redirect("account/invite_keys");
            }
            
            // Get an array of all the users invite keys
            $query = "SELECT * FROM `pcms_reg_keys` WHERE `sponser`=? AND `assigned`= 0;";
            $user_keys = $this->DB->query($query, array($this->user['id']))->fetch_array();
            
            // Prepare for output
            $data['keys'] = array();
            if( sizeof( $user_keys ) > 0 ) $data['keys'] = $user_keys;
            $this->load->view("reg_keys", $data);
            return;
        }
        
        // User isnt allowed to create keys
        redirect( 'account' );
    }

// Test Page    
    function test()
    {
        $this->load->model('Vote_Model', 'model');
        $this->model->submit($this->user['id'], 2);
        die(); 
        // Check for POST data
        if(isset($_POST['action'])) goto Process;
        
        switch($mode)
        {
            case "password":
                $this->load->view('change_password');
                break;
                
            case "email":
                $this->load->view('change_password');
                break;
                
            default:
                show_404();
        }
        
        // Main processing form
        Process:
        {
            switch($mode)
            {
                case "password":
                    $this->load->view('change_password');
                    break;
                    
                case "email":
                    $this->load->view('change_password');
                    break;
                    
                default:
                    show_404();
            }
        }
    }
}
// EOF