<?php
class Support extends Application\Core\Controller 
{
    function __construct()
    {
        parent::__construct();
    }

/*
| ---------------------------------------------------------------
| P01: Index support page
| ---------------------------------------------------------------
|
*/
    function index() 
    {
        redirect('support/howtoplay');
    }

/*
| ---------------------------------------------------------------
| P02: How to Play (Connection Guide)
| ---------------------------------------------------------------
|
*/
    function howtoplay() 
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