<?php
class Characters extends Application\Core\Controller 
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct()
    {
        parent::__construct();
    }

/*
| ---------------------------------------------------------------
| Index Page
| ---------------------------------------------------------------
|
*/
    public function index() 
    {
        // Load the page, and we are done :)
        $this->load->view('characters_index');
    }

/*
| ---------------------------------------------------------------
| View Character Profile
| ---------------------------------------------------------------
|
*/
    public function view($name = NULL, $realm = NULL) 
    {
        // XSS and Sql Injection prevention
        if(empty($name))
        {
            redirect('armory');
            return;
        }
    }
    
/*
| ---------------------------------------------------------------
| Search Characters
| ---------------------------------------------------------------
|
*/
    public function search($name = NULL) 
    {

    }
    
/*
| ---------------------------------------------------------------
| View Players Online
| ---------------------------------------------------------------
|
*/
    public function online($realm = NULL) 
    {
        
    }
}
// EOF