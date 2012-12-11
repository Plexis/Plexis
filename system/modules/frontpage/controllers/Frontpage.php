<?php

use Core\Controller;
use Library\Template;
use Library\View;
use Library\ViewNotFoundException;

class Frontpage extends Controller
{
    public function __construct()
    {
        // Make sure to call the parent contructor if you have a custom constructor
        parent::__construct();
    }
    
    public function Index()
    {
        // Load our view using the Core\Controller::loadView method
        try {
            $view = $this->loadView("main.tpl");
            $view->Set('message', $this->modulePath);
            Template::Add($view);
        }
        catch( ViewNotFoundException $e ) {
            echo "<br /><br />". $e->getMessage();
        }
    }
}