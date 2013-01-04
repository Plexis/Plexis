<?php
/*
| ---------------------------------------------------------------
| Example Module
| ---------------------------------------------------------------
*/

class Devtest extends \Core\Controller 
{

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
*/
    public function __construct()
    {
        // Normally construct the application controller
        parent::__construct(__FILE__); 
    }
    
/*
| ---------------------------------------------------------------
| Page Functions - These are viewed by users in the frontend
| ---------------------------------------------------------------
*/
    
    public function index() 
    {
        $string = "<frame>test</frame> <div>Hi!</div>";
        $Filter = new Core\XssFilter();
        $Filter->useBlacklist(true);
        $Filter->setTagsMethod( Core\XssFilter::BLACKLIST );
        Library\Template::Add($Filter->clean($string));
    }
}
?>