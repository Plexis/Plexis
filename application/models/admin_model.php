<?php
class Admin_Model extends System\Core\Model 
{
    // We want only admins to have access here
    protected $is_admin = TRUE;
/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    function __construct()
    {
        // Load required Libs
        parent::__construct();
        $this->realm = $this->load->realm();
        $this->Session = $this->load->library('Session');
        
        // Init a session var
        $this->user = $this->Session->get('user');
        
        // Make sure the user has admin access'
        if( !($this->user['is_admin'] == 1 || $this->user['is_super_admin'] == 1) )
        {
            $this->is_admin == FALSE;
            die();
        }
    }
}
// EOF