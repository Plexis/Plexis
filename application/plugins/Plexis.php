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
| Plugin: Plexis
| ---------------------------------------------------------------
|
| Main plugin for detecting whether the system needs installed
| Displays error message when install folder exists, but the system
| is installed
|
*/
namespace Plugins;

class Plexis
{
    public function pre_system()
    {
        // Check for database online, surpress errors
        $DB = load_class('Loader')->database('DB', false, true);
        
        // Check if installer files are present
        $installer = file_exists( ROOT . DS . 'install/index.php' );
        
        // Check if the install folder is still local
        if($DB == false && $installer == true)
        {
            redirect('install/index.php');
            die();
        }
        elseif( $installer == true )
        {
            // Set a nice output message for the admin telling him to delete the installer folder!
            $GLOBALS['template_messages'][] = '<div class="alert error">Install folder still exists! Please Rename or delete your install folder.</div>';
        }
    }
    
    // Un used by this plugin, but they HAVE TO BE defined!
    public function pre_controller() {}
    public function post_controller_constructor() {}
    
    // This method will auto ajax the realm status every minute so the ajax on the front end doesnt slow users down
    public function post_controller() 
    {
        // Output is sent to browser, Lets make sure our realm status is up to date
        
        /* // Dont cause an endless loop!
        if($GLOBALS['controller'] == 'ajax' && $GLOBALS['action'] == 'realms') return;
        
        // Load the cache class
        $Cache = load_class('Cache', 'Library');
        $expires = $Cache->expire_time('realm_status');
        
        // Realtime still has a 30 second timer
        if(($expires - 90) < time())
        {
            // Open a new request, so we dont have to wait for who knows how long when there is tons of logs!
            $Router = load_class('Router');
            $url = $Router->get_url_info();

            load_class('Debug')->silent_mode(true);
            $fh = fsockopen($url['http_host'], 80, $er, $es, 2);
            if($fh)
            {
                // Post the headers and post data
                $data = "action=status&realtime=1"; 
                fwrite($fh, "POST /". trim($url['site_dir'], '/') ."/ajax/realms HTTP/1.1\r\n");
                fwrite($fh, "HOST: ". $_SERVER['HTTP_HOST'] ."\r\n");
                fwrite($fh, "Content-Type: application/x-www-form-urlencoded\r\n");
                fwrite($fh, "Content-Length: " . strlen($data) . "\r\n\r\n");
                fwrite($fh, $data . "\r\n");
                
                // Dont close right away
                fclose($fh);
            }
        } */
    }
}