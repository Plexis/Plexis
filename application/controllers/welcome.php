<?php
class Welcome extends Application\Core\Controller 
{
    function __construct()
    {
        parent::__construct();
    }

    function _beforeAction() 
    {
        /*
        | You can call before and after actions, sorta like mini hooks
        | They arent nessesary, but convenient not having to make a full hook
        | Since they are loaded in the main controller, you dont need to 
        | include these functions at all in your controller.
        */
    }

    function index() 
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

    function _afterAction() 
    {
        // And here is an after action
    }
}
// EOF