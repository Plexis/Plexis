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
| P01 - Index Page
| P02 - Onlinelist
| P03 - Realmlist
|
*/
class Server extends Application\Core\Controller 
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
        redirect('server/realmlist');
    }

/*
| ---------------------------------------------------------------
| P02: Onlinelist
| ---------------------------------------------------------------
|
*/
    public function onlinelist($id = 0) 
    {
        // Get our users selected realm
        $c = get_realm_cookie();
        
        // Get our realm id if none is provieded
        if($id == 0 && $c == 0)
        {
            output_message('info', 'no_realms_installed');
            $this->load->view('blank');
            return;
        }
        
        // Absolutly set our cookie realm IF the user selected a different realm
        if($id != $c)
        {
            load_class('Input')->set_cookie('realm_id', $id);
            $_COOKIE['realm_id'] = $id;
        }
        
        // Load the datatables script and fnReload Ajax
        $this->Template->add_script('jquery.dataTables.js');
        $this->Template->add_script('jquery.dataTables.extended.js');
        
        // Build our realm select options
        $data['realm_options'] = array();
        $realms = get_installed_realms();
        foreach($realms as $realm)
        {
            $selected = '';
            if($id == $realm['id']) $selected = 'selected="selected" ';
            $data['realm_options'][] = "<option value='".$realm['id']. "' ". $selected ."'>".$realm['name']."</option>";
        }
        
        // Load the view and call it a day!
        $this->load->view('onlinelist', $data);
    }
    
/*
| ---------------------------------------------------------------
| P03: Realmlist
| ---------------------------------------------------------------
|
*/
    public function realmlist() 
    {
        // Get our users selected realm
        $c = get_realm_cookie();
        
        // Get our realm id if none is provieded
        if($c == 0)
        {
            output_message('info', 'no_realms_installed');
            $this->load->view('blank');
            return;
        }
        
        // Load the datatables script
        $this->Template->add_script('jquery.dataTables.js');
        
        // Build our realm select options
        $data['realms'] = get_installed_realms();
        
        // Load the view and call it a day!
        $this->load->view('realmlist', $data);
    }
    
/*
| ---------------------------------------------------------------
| P03: View Ralm
| ---------------------------------------------------------------
|
*/
    public function viewrealm($id = 0) 
    {
        // Get our users selected realm
        $c = get_realm_cookie();
        
        // Get our realm id if none is provieded
        if($c == 0)
        {
            output_message('info', 'no_realms_installed');
            $this->load->view('blank');
            return;
        }
        
        // Absolutly set our cookie realm IF the user selected a different realm
        if($id != $c)
        {
            load_class('Input')->set_cookie('realm_id', $id);
            $_COOKIE['realm_id'] = $id;
        }
        
        // Load the view and call it a day!
        $this->load->view('viewrealm');
    }

}
// EOF