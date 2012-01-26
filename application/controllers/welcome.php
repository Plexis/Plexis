<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
|---------------------------------------------------------------
|
| Navigation. (user CTRL + f to move quickly)
|---------------------------------------------------------------
| P01 - Index page (Frontpage)
|
*/
class Welcome extends Application\Core\Controller 
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
| P01: Index Page
| ---------------------------------------------------------------
|
*/
    public function index() 
    {
        // Load a Welcome Model
        $this->load->model('News_Model');
        
        // Get news posts
        $data['news'] = $this->News_Model->get_news_posts(5);
        
        // Loop through each news post and add stamp
        foreach($data['news'] as $key => $value)
        {
            $data['news'][$key]['posted'] = date("F j, Y, g:i a", $value['posted']);
            $data['news'][$key]['body'] = stripslashes($data['news'][$key]['body']);
        }
        
        // Load the page, and we are done :)
        $this->load->view('index', $data);
    }
}
// EOF