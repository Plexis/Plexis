<?php

namespace Core;

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
    
    // Protected methods
    protected static $protocol = 'http';
    protected static $baseurl;
    protected static $domain;
    protected static $webroot;
    protected static $queryString;
    protected static $clientIp;

    
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
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
    
/*
| ---------------------------------------------------------------
| Method: Input()
| ---------------------------------------------------------------
|
| Returns data from the 'php://input'
|
| @Return (String)
|
*/
    public static function Input($calllback = null)
    {
        return file_get_contents('php://input');
    }
    
/*
| ---------------------------------------------------------------
| Method: Method()
| ---------------------------------------------------------------
|
| Returns the request http method
|
| @Return (String)
|
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
    
/*
| ---------------------------------------------------------------
| Method: Referer()
| ---------------------------------------------------------------
|
| Returns the referer
|
| @Return (String)
|
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
    
/*
| ---------------------------------------------------------------
| Method: Query()
| ---------------------------------------------------------------
|
| Returns the url query string
|
| @Return (String)
|
*/
    public static function Query($key = null, $default = null)
    {
        if($key == null)
            return $_SERVER['QUERY_STRING'];
            
        return (array_key_exists($key, $_GET)) ? $_GET[$key] : $default;
    }
    
/*
| ---------------------------------------------------------------
| Method: Post()
| ---------------------------------------------------------------
|
| Returns the POST var specified
|
| @Return (Mixed)
|
*/
    public static function Post($key = null, $default = null)
    {
        if($key == null)
            return $_POST;
            
        return (array_key_exists($key, $_POST)) ? $_POST[$key] : $default;
    }
    
/*
| ---------------------------------------------------------------
| Method: Cookie()
| ---------------------------------------------------------------
|
| Returns the value of a cookie
|
| @Return (String)
|
*/
    public static function Cookie($key = null, $default = null)
    {
        if($key == null)
            return $_COOKIE;
            
        return (array_key_exists($key, $_COOKIE)) ? $_COOKIE[$key] : $default;
    }
    
/*
| ---------------------------------------------------------------
| Method: Accpets()
| ---------------------------------------------------------------
|
| Returns the an array of what formats the client is accepting
|
| @Return (Array | Bool)
|
*/
    public static function Accepts($type = null){}
    
/*
| ---------------------------------------------------------------
| Method: AcceptsLanguage()
| ---------------------------------------------------------------
|
| Returns the language in which the client accepts
|
| @Return (String)
|
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
    
/*
| ---------------------------------------------------------------
| Method: ClientIp()
| ---------------------------------------------------------------
|
| Returns the clients IP
|
| @Return (String)
|
*/
    // The client IP
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
    
/*
| ---------------------------------------------------------------
| Method: IsAjax()
| ---------------------------------------------------------------
|
| Returns the whether the request is an ajax request
|
| @Return (Bool)
|
*/
    public static function IsAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
    
/*
| ---------------------------------------------------------------
| Method: Header()
| ---------------------------------------------------------------
|
| Returns the value of the specified header passed
|
| @Return (String)
|
*/
    // Return header name value
    public static function Header($name)
    {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return (!empty($_SERVER[$name])) ? $_SERVER[$name] : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: BaseUrl()
| ---------------------------------------------------------------
|
| Returns the sites base url
|
| @Return (String)
|
*/
    public static function BaseUrl()
    {
        return self::$baseurl;
    }
    
/*
| ---------------------------------------------------------------
| Method: Protocol()
| ---------------------------------------------------------------
|
| Returns the current requests protocol
|
| @Return (String)
|
*/
    public static function Protocol()
    {
        return self::$protocol;
    }
}

// Init the class
Request::Init();