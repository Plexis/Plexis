<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: News_Model()
| ---------------------------------------------------------------
|
| Model for the News / Admin::news() controller
|
*/
class News_Model extends Application\Core\Model 
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
| Method: get_news_posts()
| ---------------------------------------------------------------
|
| Returns an array of news posts
|
| @Param: (Int) $limit - The number of max results returned
| @Return (Array) - An array of news posts
|
*/
    public function get_news_posts($limit = '')
    {
        // Add our limit if we have one
        (is_numeric($limit)) ? $limit = " LIMIT ".$limit : '';
        
        // Get our news posts out of the database
        $query = "SELECT `id`, `title`, `author`, `posted`, `body` FROM `pcms_news` ORDER BY id DESC ".$limit;
        $posts = $this->DB->query( $query )->fetch_array();
        
        // If we have no results, return an empty array
        if($posts == FALSE)
        {
            return array();
        }
        return $posts;
    }

/*
| ---------------------------------------------------------------
| Method: get_post()
| ---------------------------------------------------------------
|
| Gets a single news post from the DB and returns the row
|
| @Param: (Int) $id - The News post ID in the DB
| @Return (Array) - An array of the news post tcolumns
|   );
|
*/
    public function get_news_post($id)
    {
        // Get our news posts out of the database
        $query = "SELECT `id`, `title`, `author`, `posted`, `body` FROM `pcms_news` WHERE `id`=".$id;
        $post = $this->DB->query( $query )->fetch_row();
        
        // If we have no results, return an empty array
        if($post == FALSE)
        {
            return FALSE;
        }
        
        // Return Our Data
        return $post;
    }
    
/*
| ---------------------------------------------------------------
| Method: submit_news()
| ---------------------------------------------------------------
|
| Adds a news post to the database
|
| @Param: (String) $title - NEws post title
| @Param: (String) $body - The news post
| @Param: (String) $username - Username of the poster
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function submit_news($title, $body, $username = 'Admin')
    {
        // Build out insert data
        $data['title'] = $title;
        $data['author'] = $username;
        $data['posted'] = time();
        $data['body'] = $body;
        
        // Insert our post
        return $this->DB->insert('pcms_news', $data);
    }
    
/*
| ---------------------------------------------------------------
| Method: update_news()
| ---------------------------------------------------------------
|
| Update a news post in the database
|
| @Param: (String) $title - NEws post title
| @Param: (String) $body - The news post
| @Param: (String) $username - Username of the poster
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function update_news($id, $title, $body)
    {
        // Build out insert data
        $data['title'] = $title;
        $data['body'] = $body;
        
        // Insert our post
        return $this->DB->update('pcms_news', $data, "`id`=$id");
    }
    
/*
| ---------------------------------------------------------------
| Method: delete_post()
| ---------------------------------------------------------------
|
| Deletes a news post in the database
|
| @Param: (String) $title - NEws post title
| @Param: (String) $body - The news post
| @Param: (String) $username - Username of the poster
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function delete_post($id)
    {
        // Delete our post and return the result
        return $this->DB->delete('pcms_news', "`id`=$id");
    }
}
// EOF