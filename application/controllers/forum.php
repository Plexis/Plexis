<?php
class Forum extends Application\Core\Controller 
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
        
        // Load the Forum Model, instance as $this->forum
        $this->load->model('Forum_Model', 'forum');
    }

/*
| ---------------------------------------------------------------
| Index Page
| ---------------------------------------------------------------
|
*/
    public function index() 
    {
        // Get our categories
        $data = $this->forum->get_categories();
        
        // Loop through each category and get its forums
        foreach($data as $key => $catagory)
        {
            $data[$key]['forums'] = $this->forum->get_forums($category['cat_id']);
        }
        
        // Load the page, and we are done :)
        $this->load->view('forum_index', $data);
    }

/*
| ---------------------------------------------------------------
| View Forum
| ---------------------------------------------------------------
|
*/
    public function viewforum($id) 
    {
        // XSS and Sql Injection prevention
        if(!is_numeric($id) || empty($id))
        {
            redirect('forum');
            return;
        }
        
        // Load the page, and we are done :)
        $this->load->view('forum_viewforum', $data);
    }
    
/*
| ---------------------------------------------------------------
| View Topic
| ---------------------------------------------------------------
|
*/
    public function viewtopic($id) 
    {
        // XSS and Sql Injection prevention
        if(!is_numeric($id) || empty($id))
        {
            redirect('forum');
            return;
        }
        
        // Load the page, and we are done :)
        $this->load->view('forum_viewtopic');
    }
    
/*
| ---------------------------------------------------------------
| Reply
| ---------------------------------------------------------------
|
*/
    public function reply($id) 
    {
        // XSS and Sql Injection prevention
        if(!is_numeric($id) || empty($id))
        {
            redirect('forum');
            return;
        }
        
        // Load the page, and we are done :)
        $this->load->view('forum_reply');
    }
    
/*
| ---------------------------------------------------------------
| Post New Topic
| ---------------------------------------------------------------
|
*/
    public function post($id) 
    {
        // XSS and Sql Injection prevention
        if(!is_numeric($id) || empty($id))
        {
            redirect('forum');
            return;
        }
        
        // Load the page, and we are done :)
        $this->load->view('forum_post');
    }
}
// EOF