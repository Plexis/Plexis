<?php
namespace Account;

// Bring some classes into scope So we dont have to specify namespaces with each class
use Core\Controller;
use Core\Request;
use Core\Response;
use Library\Auth;
use Library\Template;

final class Logout extends Controller
{  
    /**
     * Page used to logout a user
     */
    public function index()
    {
        // If the user is a guest already, just redirect to index
        if(Auth::IsGuest())
            Response::Redirect('');
        
        // Tell the auth class to logout
        Auth::Logout();
        
        // Show a goodbye screen
        $View = Template::LoadPartial('contentbox');
        $View->set('title', 'Logout');
        $View->set('contents', $this->loadView('logout'));
        Template::Add($View);
    }
}