<?php
namespace Account;

// Bring some classes into scope So we dont have to specify namespaces with each class
use Core\Controller;
use Library\Template;

final class Account extends Controller
{
    public function index()
    {
        // Tell the parent controller we require a logged in user
        $this->requireAuth();
        
        // TODO
        Template::Message('info', 'Under Construction');
    }
}