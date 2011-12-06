<?php
class News extends Application\Core\Controller 
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
        
        // Load the News Model
        $this->load->model('News_Model', 'news');
    }

/*
| ---------------------------------------------------------------
| Index Page
| ---------------------------------------------------------------
|
*/
    public function index() 
    {
        output_message('success', 'testing');
        output_message('warning', 'testing');
        output_message('error', 'testing');
        output_message('info', 'testing');
        // Get news posts
        $data['news'] = $this->news->get_news_posts();
        
        // Load the page, and we are done :)
        $this->load->view('index', $data);
    }

/*
| ---------------------------------------------------------------
| View News Post Page
| ---------------------------------------------------------------
|
*/
    public function view($id) 
    {
        // XSS and Sql Injection prevention
        if(!is_numeric($id) || empty($id))
        {
            $this->load->view('invalid');
            return;
        }
        
        // Get news post
        $post = $this->news->get_post($id);
        
        // Show news error page if post not found in DB
        if($post == FALSE)
        {
            $this->load->view('invalid');
            return;
        }
        
        // Create our data array
        $data = array(
            'id' => $id,
            'title' => $post['title'],
            'author' => $post['author'],
            'posted' => date("F j, Y, g:i a", $post['posted']),
            'body' => $post['body']
        );
        
        // Load the page, and we are done :)
        $this->load->view('view', $data);
    }
}
// EOF