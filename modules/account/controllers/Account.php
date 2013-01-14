<?php
namespace Account;

// Bring some classes into scope So we dont have to specify namespaces with each class
use Core\Controller;
use Core\Request;
use Core\Response;
use Library\Auth;
use Library\Template;
use Library\View;
use Library\ViewNotFoundException;

final class Account extends Controller
{
    /**
     * The account model ... model
     */
    protected $AccountModel;
    
    public function __construct($Module)
    {
        // Construct the parent controller, providing our module object
        parent::__construct($Module);
    }
    
    public function index()
    {
        // Tell the parent controller we require a logged in user
        $this->requireAuth();
        
        // TODO
    }
    
    /**
     * Page used to login a user
     */
    public function login()
    {
        // If we have no post data, then we check for a valid session.
        // If one exists, redirect to the account screen, otherwise, login screen
        $post = Request::Post('username', false);
        if($post === false)
        {
            // Just have the parent controller pop open the login screen :)
            $this->requireAuth();
            
            // Incase we get here, uesr is logged in, so redirect!
            Response::Redirect('account', 0, 307);
            return;
        }
        
        // Call the account model, as it proccesses the login
        $this->loadModel('AccountModel');
        $result = $this->AccountModel->doLogin();
        
        // Check result
        if(!$result['success'])
        {
            Template::Message('error', $result['message']);
            $this->requireAuth(); // Show login screen yet again! Yay!
        }
        else
        {
            // So the login was successful, now we must figure our redirection url
            $referer = Request::Referer();
            if(strpos("/account/login", $referer) !== false)
                Response::Redirect('account', 0, 307);
            else
                Response::Redirect($referer, 0, 307);
        }
    }
    
    /**
     * Page used to logout a user
     */
    public function logout()
    {
        // If the user is a guest already, just redirect to index
        if(Auth::IsGuest())
            Response::Redirect('');
        
        // Tell the auth class to logout
        Auth::Logout();
        
        // Show a goodbye screen
        $View = $this->loadTemplateView('contentbox');
        $View->Set('title', 'Logout');
        $View->Set('contents', $this->loadView('logout'));
        Template::Add($View);
    }
    
    public function ajax($method, $param)
    {
        $this->loadController('Ajax')->{$method}($param);
        die;
    }
}