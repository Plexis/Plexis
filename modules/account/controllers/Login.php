<?php
namespace Account;

// Bring some classes into scope So we dont have to specify namespaces with each class
use Core\Controller;
use Core\Request;
use Core\Response;
use Library\Auth;
use Library\Template;

final class Login extends Controller
{
    public function index()
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
}