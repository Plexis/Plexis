<?php
/*
| ---------------------------------------------------------------
| Example Module
| ---------------------------------------------------------------
*/

class Devtest extends Core\Controller 
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
    
    public function intsize()
    {
        echo PHP_INT_MAX;
        Plexis::RenderTemplate(false);
    }
    
    public function dirtest()
    {
        require path( SYSTEM_PATH, 'core', 'io', 'DirectoryInfo.php');
        require path( SYSTEM_PATH, 'core', 'io', 'FileInfo.php');
        
        $path1 = truePath('C:/users/steve/desktop/test/');
        $path2 = truePath('C:/users/steve/desktop/test2/');
        
        $d = new Core\IO\DirectoryInfo($path1);
        $d->moveTo($path2);
        var_dump($d);
        
        echo microtime(1) - TIME_START;
        Plexis::RenderTemplate(false);
    }
}
?>