<?php
namespace Frontpage;

// Bring some classes into scope So we dont have to specify namespaces with each class
use Core\Controller;
use Core\Module;
use Library\Template;
use Library\View;
use Library\ViewNotFoundException;

final class Frontpage extends Controller
{
    public function __construct($Module)
    {
        // Construct the parent controller, providing our module object
        parent::__construct($Module);
    }
    
    public function Index()
    {
        // Load our view using the Core\Controller::loadView method
        try {
            // First load our page view
            $view = $this->loadView("main");
            $view->set('message', 'Module path: '. $this->modulePath .'<br />Module HTTP URI: '. $this->moduleUri);
            
            // Load a content box next, placing our module view as the contents of the box
            $box = $this->loadTemplateView("contentbox");
            $box->set('title', 'Test Title');
            $box->set('contents', $view->render());
            
            // Add the finished content box to the template
            Template::Add($box);
        }
        catch( ViewNotFoundException $e ) {
            echo "<br /><br />". $e->getMessage();
        }
    }
}