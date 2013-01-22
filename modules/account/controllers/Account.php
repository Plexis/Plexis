<?php
namespace Account;

// Bring some classes into scope So we dont have to specify namespaces with each class
use Core\Controller;
use Library\Template;

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
        Template::Message('info', 'Under Construction');
    }
}