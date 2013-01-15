<?php  
/**
 * Core 404 handling Class
 */
namespace Error;

use Core\Controller;
use Core\Config;
use Core\Response;
use Core\Request;
use Library\Template;
 
final class SiteOffline extends Controller
{
    /**
     * For 404's and 403's, plexis will always call upon the
     * "index" method to handle the request.
     */
    public function index()
    {
        // Clean all current output
        ob_clean();
        
        // Reset all headers, and set our status code to 503 "Service Unavailable"
        Response::Reset();
        Response::StatusCode(503);
        
        // Get our 404 template contents
        $View = $this->loadView('site_offline');
        $View->set('site_url', Request::BaseUrl());
        $View->set('root_dir', $this->moduleUri);
        $View->set('title', Config::GetVar('site_title', 'Plexis'));
        $View->set('template_url', Template::GetThemeUrl());
        Response::Body($View);
        
        // Send response, and Dir
        Response::Send();
        die;
    }
    
    public function ajax()
    {
        // Clean all current output
        ob_clean();
        
        // Reset all headers, and set our status code to 503 "Service Unavailable"
        Response::Reset();
        Response::StatusCode(503);
        Response::Body( json_encode(array('message' => 'Site Currently Offline')) );
        Response::Send();
        die;
    }
}