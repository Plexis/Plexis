<?php  
/**
 * Core 403 handling Class
 */
use Core\Config;
use Core\Response;
use Core\Request;
use Library\Template;
 
class Show403 extends Core\Controller
{
    /**
     * For 404's and 403's, plexis will always call upon the
     * "index" method to handle the request.
     */
    public function index()
    {
        // Clean all current output
        ob_clean();
        
        // Reset all headers, and set our status code to 404
        Response::Reset();
        Response::StatusCode(403);
        
        // Get our 404 template contents
        $View = $this->loadView('403');
        $View->set('title', Config::GetVar('site_title', 'Plexis'));
        $View->set('site_url', Request::BaseUrl());
        $View->set('root_dir', $this->moduleUri);
        $View->set('uri', ltrim(Request::Query('uri'), '/'));
        $View->set('template_url', Template::GetThemeUrl());
        Response::Body($View);
        
        // Send response, and die
        Response::Send();
    }
    
    public function ajax()
    {
        // Clean all current output
        ob_clean();
        
        // Reset all headers, and set our status code to 403
        Response::Reset();
        Response::StatusCode(403);
        Response::Body( json_encode(array('message' => 'Forbidden')) );
        Response::Send();
    }
}