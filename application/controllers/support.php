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
| P01 - Index support Page
| P02 - How To Play
|
*/
class Support extends Application\Core\Controller 
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
| P01: Index support page
| ---------------------------------------------------------------
|
*/
    public function index() 
    {
        redirect('support/howtoplay');
    }

/*
| ---------------------------------------------------------------
| P02: How to Play (Connection Guide)
| ---------------------------------------------------------------
|
*/
    public function howtoplay() 
    {
        // Get our current language
        $this->Input = load_class('Input');
        $cookie = $this->Input->cookie('language');
        
        // Check for a cookie
        if($cookie == FALSE)
        {
            $language = config('default_language');
        }
        else
        {
            // Make sure the language exists still
            if( file_exists(APP_PATH . DS . 'language' . DS . $cookie . DS . 'howtoplay.html') )
            {
                $language = $cookie;
            }
            else
            {
                $language = config('default_language');
            }
        }
        
        // Set up our hoew to play page
        $data['text'] = file_get_contents( APP_PATH . DS . 'language' . DS . $language . DS . 'howtoplay.html' );
        $data['logon_server'] = config('logon_server');
        $data['register_link'] = SITE_URL .'/account/register';
        $this->load->view('howtoplay', $data);
    }
}
// EOF