<?php

class Forum_Model extends Application\Core\Model 
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
| Method: get_catagories()
| ---------------------------------------------------------------
|
| This method is used to get an array of categories to the forums
|
*/    
    public function get_categories()
    {
        // Get the list of forum categories from the DB
        return $this->DB->query("SELECT * FROM `pcms_forum_categories`")->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Method: get_forums()
| ---------------------------------------------------------------
|
| This method is used to get an array of forums that belong to
| a category
|
| @Param (Int) $c_id - The id of the category
|
*/    
    public function get_forums($c_id)
    {
        // Get the list of forum categories from the DB
        $query = "SELECT * FROM `pcms_forum_forums` WHERE cat_id=?";
        return $this->DB->query($query, array($c_id))->fetch_array();
    }
 
/*
| ---------------------------------------------------------------
| Method: add_topic()
| ---------------------------------------------------------------
|
| This method adds a users posted topic to a forum
|
| @Param (Int) $forum - The id of the forum the topic will belong to
| @Param (String) $subject - The subject of the post
| @Param (String) $content - The content string of the post
| @Param (String) $poster - The poster information
| @Param (String) $flags - The flags for the post (Sticky, Global, Closed)
| @Param (String) $permissions - Permissions (not sure yet)
| @Return (Bool) TRUE on success, otherwise FALSE
|
*/  
    public function add_topic($forum, $subject, $content, $poster, $flags = NULL, $permissions = NULL)
    {
        // Init some globals
        $sticky = $closed = $global = 0;
        
        // check for missing or emtpy strings
        if( empty($forum) || empty($subject) || empty($content) )
        {
            return FALSE;
        }
        
        // Check to see what flags we have
        if($flags != NULL)
        {
            $flags = explode('|', $flags);
            if(in_array('sticky', $flags)) $sticky = 1;
            if(in_array('closed', $flags)) $closed = 1;
            if(in_array('global', $flags)) $global = 1;		
        }
        
        // Inserts
        // Start our transaction
        $this->DB->beginTransaction();
        $time = time();
        
        // Insert the topic
        $insert_topic = $this->DB->insert("pcms_forum_topics", array(
            'topic_subject' => $subject,
            'topic_by' => $poster,
            'last_post_time' => $time,
            'topic_forum' => $forum,
            'is_sticky' => $sticky,
            'is_closed' => $closed,
            'is_global' => $global
        ));
        
        // Check to see if the topic insert is a success
        if($insert_topic != FALSE)
        {
            // Get our topic ID from the last commit
            $topicid = $this->DB->last_insert_id();
            
            // Insert the actual post
            $insert_post = $this->DB->insert("pcms_forum_posts", array(
                'post_content' => $content,
                'post_by' => $poster,
                'post_time' => $time,
                'post_topic' => $topicid
            ));
            
            // Last check to see if the post was successfull
            if($insert_post != FALSE)
            {
                // Get the insert post ID so we can update the forum last post info
                $lastpost_id = $this->DB->last_insert_id();
                
                // Update the forum count
                $rows = $this->DB->update("pcms_forum_forums", array( 
                    'total_topics' => "(`total_topics` + 1)", 
                    'total_posts' => "(`total_posts` + 1)",
                    'last_post_id' => $lastpost_id,
                    'last_topic_id' => $topicid,
                    'last_topic_title' => $subject,
                    'last_post_time' => $time,
                    'last_post_poster' => $poster
                ), "`forum_id`='".$forum."'");
                
                if($rows != 0)
                {
                    // Tell the database to commit the transaction
                    $this->DB->commit();
                    return TRUE;
                }
            }
        }

        // If we are here, we failed
        $this->DB->rollBack();
        return FALSE;
    }

/*
| ---------------------------------------------------------------
| Method: add_post()
| ---------------------------------------------------------------
|
| This method is used to take a new topic post, and post it to
|   the database, do all the updating etc
|
| @Param (Int) $topic_id - The id of the topic we are replying to
| @Param (String) $content - The content string of the post
| @Param (String) $poster - The poster information
| @Return (Bool) TRUE on success, otherwise FALSE
|
*/ 	
    function add_post($topic_id, $content, $poster)
    {
        // check for missing or emtpy strings
        if( empty($topic_id) || empty($poster) || empty($content) )
        {
            return FALSE;
        }
        
        // get our topic subject and forum id
        $query = "SELECT `topic_subject`, `topic_forum` FROM `pcms_forum_topics` WHERE `topic_id`=?";
        $topic = $this->DB->query($query, array($topic_id))->fetch_row();
        
        // False return?
        if($topic != FALSE)
        {
            // Start our transaction
            $this->DB->beginTransaction();
            $time = time();
        
            // Insert the actual post
            $insert_post = $this->DB->insert("pcms_forum_posts", array(
                'post_content' => $content,
                'post_by' => $poster,
                'post_time' => $time,
                'post_topic' => $topic_id
            ));
            
            // Check the post
            if($insert_post != FALSE)
            {
                // Get our post ID from last insert
                $postid = $this->DB->last_insert_id();
            
                // Insert into topics
                $update_topic = $this->DB->update("pcms_forum_topics", array(
                    'last_post_id' => $postid,
                    'last_post_by' => $poster,
                    'last_post_time' => $time,
                    'topic_replies' => "(`topic_replies` + 1)"
                ), "`topic_id` = ".$topic_id);
                
                // Check the update, if successfull, we need to update the forum as well.
                if($update_topic != FALSE)
                {
                    // Update the forum count
                    $rows = $this->DB->update("pcms_forum_forums", array( 
                        'total_posts' => "(`total_posts` + 1)",
                        'last_post_id' => $postid,
                        'last_topic_id' => $topic_id,
                        'last_topic_title' => $topic['topic_subject'],
                        'last_post_time' => $time,
                        'last_post_poster' => $poster
                    ), "`forum_id`= ".$topic['topic_forum']);
                    
                    if($rows != 0)
                    {
                        // Tell the database to commit the transaction
                        $this->DB->commit();
                        return TRUE;
                    }
                }
            }
            
            // If we are here, we failed
            $this->DB->rollBack();
        }
        
        // Send back the bad news
        return FALSE;
    }
}
// EOF