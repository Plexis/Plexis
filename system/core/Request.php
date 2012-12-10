<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Request.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Request
 */
namespace Core;

/**
 * This class provides information for the current Request. Such information
 * like all the Post and GET data, the URI string, the Remote IP, Referer,
 * the base URL, website root, and more.
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class Request
{
    // Protocols
    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';
    const PROTOCOL_FTP = 'ftp';
    const PROTOCOL_SSL = 'ssl';
    
    // Request methods
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';    
    
    /**
     * Current protocol
     * @var string
     */
    protected static $protocol = 'http';
    
    /**
     * the site's base url (the root of the website)
     * @var string
     */
    protected static $baseurl;
    
    /**
     * Http domain name (no trailing paths after the .com)
     * @var string
     */
    protected static $domain;
    
    /**
     * The web root is the trailing path after the domain name.
     * The base url is the Domain name, plus the webroot
     * @var string
     */
    protected static $webroot;
    
    /**
     * The query string passed with the request
     * @var string
     */
    protected static $queryString;
    
    /**
     * The remote IP address connected to this request
     * @var string
     */
    protected static $clientIp;

    
    /**
     * Class Constructor (called automatically)
     *
     * Initializes the class properties
     * @return void
     */
    public static function Init()
    {
        if(empty(self::$domain))
        {
            // Define our domain and webroot
            self::$domain = rtrim($_SERVER['HTTP_HOST'], '/');
            self::$webroot = dirname( $_SERVER['PHP_SELF'] );
            
            // Detect our protocol
            if(isset($_SERVER['HTTPS']))
            {
                $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
                self::$protocol = ($secure) ? 'https' : 'http';
            }
            else
            {
                self::$protocol = 'http';
            }
            
            // build our base url
            $site_url = self::$domain .'/'. self::$webroot;
            while(strpos($site_url, '//') !== false) $site_url = str_replace('//', '/', $site_url);
            self::$baseurl = str_replace( '\\', '', self::$protocol .'://' . rtrim($site_url, '/') );
        }
    }
    
    /**
     * Returns data from the 'php://input'
     *
     * @return string
     */
    public static function Input()
    {
        return file_get_contents('php://input');
    }
    
    /**
     * Returns the request http method (GET, POST, PUT etc)
     *
     * @return string
     */
    public static function Method()
    {
        $val = null;
        if(isset($_SERVER['REQUEST_METHOD']))
            $val = strtoupper($_SERVER['REQUEST_METHOD']);
        elseif(getenv('REQUEST_METHOD') !== false)
            $val = strtoupper(getenv('REQUEST_METHOD'));
            
        return $val;
    }
    
    /**
     * Returns the reffering website url
     *
     * @return string
     */
    public static function Referer()
    {
        $ref = null;
        if(isset($_SERVER['HTTP_X_FORWARDED_HOST']))
            $ref = $_SERVER['HTTP_X_FORWARDED_HOST'];
        elseif(isset($_SERVER['HTTP_REFERER']))
            $ref = $_SERVER['HTTP_REFERER'];
        
        return $ref;
    }
    
    /**
     * Returns the url query string
     *
     * @param string $key The GET array id to return. Leave null to return all GET data
     * @param mixed $default The default return value if the GET array key doesnt
     *    exist. Default is null.
     * @return string|string[]|mixed Returns $default if the GET key doesnt exist. Returns a
     *   string[] if no $key is provided, or the value of $key if the array key exists
     */
    public static function Query($key = null, $default = null)
    {
        if($key == null)
            return $_SERVER['QUERY_STRING'];
            
        return (array_key_exists($key, $_GET)) ? $_GET[$key] : $default;
    }
    
    /**
     * Returns the POST var specified, or all POST data
     *
     * @param string $key The POST array id to return. Leave null to return all POST data
     * @param mixed $default The default return value if the POST array key doesnt
     *    exist. Default is null.
     * @return string|string[]|mixed Returns $default if the POST key doesnt exist. Returns a
     *   string[] if no $key is provided, or the value of $key if the array key exists.
     */
    public static function Post($key = null, $default = null)
    {
        if($key == null)
            return $_POST;
            
        return (array_key_exists($key, $_POST)) ? $_POST[$key] : $default;
    }
    
    /**
     * Returns the Cookie name specified, or all Cookie data
     *
     * @param string $key The cookie name to return. Leave null to return all cookie data
     * @param mixed $default The default return value if the Cookie name doesnt
     *    exist. Default is null.
     * @return string|string[]|mixed Returns $default if the Cookie name doesnt exist. Returns a
     *   string[] if no $key is provided, or the value of $key if the cookie exists.
     */
    public static function Cookie($key = null, $default = null)
    {
        if($key == null)
            return $_COOKIE;
            
        return (array_key_exists($key, $_COOKIE)) ? $_COOKIE[$key] : $default;
    }
    
    /**
     * Returns the an array of what formats the client is accepting
     *
     * @param string $type The type to return
     * @todo Finish the method, and provide better description
     * @return void
     */
    public static function Accepts($type = null){}
    
    /**
     * Returns a string or string[] of what languages the client accepts
     *
     * @param string $lang If a language is provided here, the method will return
     *    true or false based on whehter the client accepts the language
     * @return string|string[]|bool Returns the language, or an array of
     * languages the client accpets. If $lang is set, then this method returns
     * a bool based on whehter the client accepts the language
     */
    public static function AcceptsLanguage($lang = null)
    {
        $accepts = preg_split('/[;,]/', self::Header('Accept-Language'));
        foreach($accepts as &$accept) 
        {
            $accept = strtolower($accept);
            if(strpos($accept, '_') !== false)
                $accept = str_replace('_', '-', $accept);
        }
        return ($lang === null) ? $accepts : in_array($lang, $accepts);
    }
    
    /**
     * Returns the Remote connected IP address
     *
     * @return string The validated remote IP address. Returns 0.0.0.0 if
     *   the IP address could not be determined
     */
    public static function ClientIp()
    {
        // Return it if we already determined the IP
        if(empty(self::$clientIp))
        {  
            // Check to see if the server has the IP address
            if(isset($_SERVER['HTTP_CLIENT_IP']) && isValidIp($_SERVER['HTTP_CLIENT_IP']))
            {
                self::$clientIp = $_SERVER['HTTP_CLIENT_IP'];
            }
            elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                // HTTP_X_FORWARDED_FOR can be an array og IPs!
                $ips = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach($ips as $ip_add) 
                {
                    if(isValidIp($ip_add))
                    {
                        self::$clientIp = $ip;
                        break;
                    }
                }
            }
            elseif(isset($_SERVER['HTTP_X_FORWARDED']) && isValidIp($_SERVER['HTTP_X_FORWARDED']))
            {
                self::$clientIp = $_SERVER['HTTP_X_FORWARDED'];
            }
            elseif(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && isValidIp($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            {
                self::$clientIp = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            }
            elseif(isset($_SERVER['HTTP_FORWARDED_FOR']) && isValidIp($_SERVER['HTTP_FORWARDED_FOR']))
            {
                self::$clientIp = $_SERVER['HTTP_FORWARDED_FOR'];
            }
            elseif(isset($_SERVER['HTTP_FORWARDED']) && isValidIp($_SERVER['HTTP_FORWARDED']))
            {
                self::$clientIp = $_SERVER['HTTP_FORWARDED'];
            }
            elseif(isset($_SERVER['HTTP_VIA']) && isValidIp($_SERVER['HTTP_VIAD']))
            {
                self::$clientIp = $_SERVER['HTTP_VIA'];
            }
            elseif(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
            {
                self::$clientIp = $_SERVER['REMOTE_ADDR'];
            }

            // If we still have a false IP address, then set to 0's
            if(empty(self::$clientIp)) self::$clientIp = '0.0.0.0';
        }
        return self::$clientIp;
    }
    
    /**
     * Returns the whether the request is an ajax request
     *
     * @return bool If the requeset is an ajax request (HTTP_X_REQUESTED_WITH => xmlhttprequest)
     */
    public static function IsAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
    
    /**
     * Returns the value of the specified header passed
     *
     * @param string $name The header name to be returned
     * @return string|bool Returns false if the header isnt set
     */
    public static function Header($name)
    {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return (!empty($_SERVER[$name])) ? $_SERVER[$name] : false;
    }
    
    /**
     * Returns the site's base URL
     *
     * @return string
     */
    public static function BaseUrl()
    {
        return self::$baseurl;
    }
    
    /**
     * Returns the current requests protocol
     *
     * @return string
     */
    public static function Protocol()
    {
        return self::$protocol;
    }
}

// Init the class
Request::Init();