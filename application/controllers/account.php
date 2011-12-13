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
                    // Success
                    (isset($_SERVER['HTTP_REFERER'])) ? redirect( $_SERVER['HTTP_REFERER'] ) : $this->load->view('login_success');
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
        (isset($_SERVER['HTTP_REFERER'])) ? redirect( $_SERVER['HTTP_REFERER'] ) : $this->load->view('logout');
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
            $this->validation->set( array(
                'username' => 'required|pattern[(^[A-Za-z0-9_-]{3,24}$)]', 
                'password' => 'required|min[3]|max[24]',
                'password2' => 'required|min[3]|max[24]',
                'email' => 'required|email',
                'sa' => 'required|min[3]|max[24]'
                ) 
            );
            
            // If everything passes validation, we are good to go
            if( $this->validation->validate() == TRUE )
            {
                // Check for captcha validation
                if( config('enable_captcha') == TRUE )
                {
                    $captcha = strtolower( $this->Input->post('captcha') );
                    if($captcha != strtolower($_SESSION['Captcha']))
                    {
                        output_message('error', 'captcha_incorrect');
                        $this->load->view('register');
                        return;
                    }
                }
                
                // Use the XSS filter on these!
                $username = $this->Input->post('username', TRUE);
                $password = $this->Input->post('password', TRUE);
                $password2 = $this->Input->post('password2', TRUE);
                $email = $this->Input->post('email', TRUE);
                $sq = $this->Input->post('sq');
                $sa = $this->Input->post('sa', TRUE);
                
                // Check that the 2 passwords matched
                if($password != $password2)
                {
                    output_message('error', 'reg_failed_passwords_different');
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
                        $this->load->view('register');
                        return;
                    }
                }
                
                // Use the AUTH class to register the user officially
                if( $this->Auth->register($username, $password, $email, $sq, $sa) == TRUE )
                {
                    // Remove registration key IF enabled
                    if( config('reg_registration_key') == TRUE )
                    {
                        $this->Input->set_cookie('reg_key', $key, (time() -1));
                        $this->DB->delete('pcms_reg_keys', "`key`='".$key."'");
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
| Email Verification Page
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
| Account Recovery
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
                    if($this->user['logged_in'] == FALSE) goto Form;
                    
                    // Make sure Users QA isnt set.. If it is then he shouldnt be here!
                    if($this->user['_account_recovery'] == TRUE) redirect('account');
                    
                    // Check for posted data
                    if(isset($_POST['action']))
                    {
                        goto Process;
                    }
                    
                    // Get our questions and load the page
                    $data['secret_questions'] = get_secret_questions();
                    $this->load->view('secret_questions', $data);
                    break;
                
                default:
                    if($this->user['logged_in'] == FALSE) goto Form;
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
            if(isset($_POST['action']))
            {
                goto Process;
            }
        }
        
        
        // Recovery Form
        Form:
        {
            $this->load->view('recover');
            return;
        }
        
        // Our process post processing
        Process:
        {
            if(!isset($_POST['action'])) goto Form;
            
            switch($_POST['action'])
            {
                case "set":
                    
                    // Load the input class and use the XSS filter on these!
                    $this->Input = load_class('Input');
                    $sq = $this->Input->post('sq', TRUE);
                    $sa = $this->Input->post('sa', TRUE);

                    // Secret question / answer processing
                    if($sq != NULL && $sa != NULL)
                    {
                        $array = array(
                            'id' => $sq,
                            'answer' => $sa,
                            'email' => $this->user['email']
                        );
                        $secret = base64_encode( serialize($array) );
                        
                        // Update the DB
                        $input = $this->DB->update('pcms_accounts', array('_account_recovery' => $secret), "`id`='".$this->user['id']."'");
                        
                        // Process the result
                        if($input == TRUE)
                        {
                            // Fetch account data from the realm
                            $data = $this->realm->fetch_account($this->user['id']);
                            
                            // Load the page, and we are done :)
                            output_message('success', 'account_recovery_set_success');
                            $this->load->view('index', $data);
                            return;
                        }
                        else
                        {
                            output_message('error', 'account_recovery_set_failed');
                            $this->load->view('blank');
                            return;
                        }
                    }
                    else
                    {
                        output_message('error', 'submit_failed_fields_empty');
                        goto Form;
                    }
                    break;
                    
                case "recover":
                    // Get our current step
                    if(isset($_POST['step']))
                    {
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
                                    
                                    // Load secret question
                                    $query = 'SELECT `email`, `_account_recovery` FROM `pcms_accounts` WHERE `username` LIKE ?';
                                    $info = $this->DB->query( $query, array($username))->fetch_row();
                                    if(!$info)
                                    {
                                        output_message('error', 'username_doesnt_exist');
                                        goto Form;
                                    }
                                    
                                    // Make sure the emails match!
                                    if($info['email'] != $email)
                                    {
                                        output_message('error', 'account_recover_invalid_email');
                                        goto Form;
                                    }
                                    
                                    // Make sure that the account recover is set
                                    if($info['_account_recovery'] == NULL)
                                    {
                                        output_message('error', 'account_recover_failed_not_set');
                                        $this->load->view('blank');
                                    }
                                    
                                    // Decrypt the recovery information, and request it
                                    $data = unserialize( base64_decode($info['_account_recovery']) );
                                    $questions = get_secret_questions('array', TRUE);
                                    $question = $questions[ $data['id'] ];
                                    
                                    // Make our new page data and load the page
                                    $data = array(
                                        'question' => $question,
                                        'username' => $username
                                    );
                                    $this->load->view('recover_step2', $data );
                                }
                                else
                                {
                                    output_message('error', 'form_validation_failed');
                                    goto Form;
                                }
                                break;

                            case 2:
                                    // Make sure we have post data
                                    if(!isset($_POST['answer'])) goto Form;
                                    
                                    // load the input class
                                    $this->Input = load_class('Input');
                                    $username = $this->Input->post('username', TRUE);
                                    $answer = $this->Input->post('answer', TRUE);
                                    
                                    // Make sure the user posted something!
                                    if($answer == NULL)
                                    {
                                        output_message('error', 'form_validation_failed');
                                        $data = array(
                                            'question' => $question,
                                            'username' => $username
                                        );
                                        $this->load->view('recover_step2', $data );
                                        return;
                                    }
                                    
                                    // Load secret question
                                    $query = 'SELECT `id`, `email`, `_account_recovery` FROM `pcms_accounts` WHERE `username` LIKE ?';
                                    $info = $this->DB->query( $query, array($username))->fetch_row();
                                    if(!$info)
                                    {
                                        output_message('error', 'username_doesnt_exist');
                                        goto Form;
                                    }
                                    
                                    // Make sure that the account recover is set
                                    if($info['_account_recovery'] == NULL)
                                    {
                                        output_message('error', 'account_recover_failed_not_set');
                                        $this->load->view('blank');
                                    }
                                    
                                    // Decrypt the recovery information, and request it
                                    $data = unserialize( base64_decode($info['_account_recovery']) );
                                    
                                    // Check that the secret answer was correct
                                    if( trim( strtolower($answer) ) == trim( strtolower($data['answer']) ) )
                                    {
                                        // Load the account model as it holds the code to change the password etc
                                        $this->load->model('account_model', 'account');
                                        $result = $this->account->process_recovery($info['id'], $username, $info['email']);
                                        
                                        // The message will be there if we whether we failed or succeded
                                        $this->load->view('blank');
                                        return;
                                        
                                    }
                                    else
                                    {
                                        output_message('error', 'account_recover_failed_wrong_answer');
                                        $data = array(
                                            'question' => $question,
                                            'username' => $username
                                        );
                                        $this->load->view('recover_step2', $data );
                                        return;
                                    }
                                    break;
                                
                            default:
                                goto Form;
                                break;
                            
                        }
                    }
                    else
                    {
                        goto Form;
                    }
                    break;
                    
                default:
                    goto Form;
                    break;
            }
        }
    }

/*
| ---------------------------------------------------------------
| Captcha Image Page
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

// Test Page    
    function test()
    {
        // Setup our variables and load our extensions
        $site_title = config('site_title');
        $site_email = config('site_email');
        $lang = load_language_file('emails');
        $this->load->library('email');
        $this->load->model('Account_model', 'account');
        
        // generate our random account verification code
        $genkey = $this->account->create_key('wilson.steven10@yahoo.com');
        $href = SITE_URL . "/account/activate/".$genkey;
        $message = vsprintf( $lang['email_activate_message'], array( 'Makaveli', $site_title, $href, $href ) );
        
        // Build the email
        $this->email->to('thasource.org@hotmail.com', 'Makaveli');
        $this->email->from( $site_email, $site_title );
        $this->email->subject( $lang['email_activate_subject'] );
        $this->email->message( $message );
        $sent = $this->email->send();
        
        // Check if our email sent correctly
        if($sent == TRUE)
        {
            output_message('success', 'reg_success_verfy_required');
            $this->load->view('blank');
        }
        else
        {
            output_message('warning', 'reg_success_email_error');
            $this->load->view('blank');
        }
    }
}
// EOF