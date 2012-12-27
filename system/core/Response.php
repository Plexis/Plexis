<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Response.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Response
 * @contains    OutputSentException
 */
namespace Core;

/**
 * This class is used to send a proper formated reponse to the client.
 * You can set headers, cookies, status codes, and protocol within
 * this class.
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class Response
{
    /**
     * HTTP protocol 1.0
     */
    const HTTP_10 = 'HTTP/1.0';
    
    /**
     * HTTP protocol 1.1
     */
    const HTTP_11 = 'HTTP/1.1';
    
    /**
     * Status code to be returned in the response
     * @var int
     */
    protected static $status = 200;
    
    /**
     * Response Protocol (HTTP/1.0 | 1.1)
     * @var string
     */
    protected static $protocol = self::HTTP_11;
    
    /**
     * Content encoding
     * @var string
     */
    protected static $charset = 'UTF-8';
    
    /**
     * Content Mime Type
     * @var string
     */
    protected static $contentType = 'text/html';
    
    /**
     * Array of headers to be sent with the response
     * @var string[]
     */
    protected static $headers = array();
    
    /**
     * Array of cookies to be sent with the response
     * @var string[]
     */
    protected static $cookies = array();
    
    /**
     * The response body (contents)
     * @var string
     */
    protected static $body = null;
    
    /**
     * Array of cahce directives to be sent with the response
     * @var string[]
     */
    protected static $cacheDirectives = array();
    
    /**
     * Used to determine if output / headers have been sent already
     * @var bool
     */
    protected static $outputSent = false;
    
    /**
     * Array of $statusCode => $description
     * @var string[]
     */
    protected static $statusCodes = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    
    /**
     * This method takes all the response headers, cookies, and current
     * buffered contents, and sends them back to the client. After this
     * methid is called, any output will most likely cause a content length
     * error for our client.
     *
     * @return void
     */
    public static function Send()
    {
        // Make sure that if we are redirecting, we set the corrent code!
        if (isset(self::$headers['Location']) && self::$status == 200)
            self::$status = 302;
        
        // If the status code is 204 or 304, there should be no contents
        if(self::$status == 204 || self::$status == 304)
            self::$body = '';
        
        // Send data in order
        self::SendHeader(self::$protocol ." ". self::$status ." ". self::$statusCodes[self::$status]);
        self::SendCookies();
        self::SendContentType();
        self::SendContentLength();
        foreach (self::$headers as $key => $value) 
            self::SendHeader($key, $value);

        self::SendBody();
        
        // Set local var that output has been sent
        self::$outputSent = true;
        
        // Disable output buffering
        ob_end_flush();
    }
    
    /**
     * Sets or returns the body of the response, based on
     * if a variable is passed setting the contents or not.
     *
     * @param string $content The body contents. Leave null if retrieving
     *   the current set contents.
     * @return string|void If $content is left null, the current
     *   contents are returned
     */
    public static function Body($content = null)
    {
        // Are we setting or retrieving?
        if(empty($content))
            return self::$status;
            
        // Make sure the data wasnt sent already
        if(self::$outputSent)
            throw new OutputSentException('Cannot set body contents because the response headers have already been sent.');
            
        self::$body = (string) $content;
    }
    
    /**
     * Appends data to the current body
     *
     * @param string $content The body contents to append.
     * @return void
     */
    public static function AppendBody($content)
    {
        // Make sure the data wasnt sent already
        if(self::$outputSent)
            throw new OutputSentException('Cannot append body contents because the response headers have already been sent.');
            
        self::$body .= (string) $content;
    }
    
    /**
     * Sets or returns the status code
     *
     * @param int $code The status code to be set
     * @return int|void If $code is left null, the current status
     *   code is returned
     */
    public static function StatusCode($code = null)
    {
        // Are we setting or retrieving?
        if(empty($code))
        {
            return self::$status;
        }
        elseif(is_numeric($code) && array_key_exists($code, self::$statusCodes))
        {
            // Make sure the data wasnt sent already
            if(self::$outputSent)
                throw new OutputSentException('Cannot set body contents because the response headers have already been sent.');
            
            self::$status = $code;
            return true;
        }
        else
            return false;
    }
    
    /**
     * Sets or returns the content type
     *
     * @param string $val The content type to be set
     * @return string|void If $val is left null, the current content
     *   type is returned
     */
    public static function ContentType($val = null)
    {
        // Are we setting or retrieving?
        if($val == null)
            return self::$contentType;
            
        // Make sure the data wasnt sent already
        if(self::$outputSent)
            throw new OutputSentException('Cannot set content type because the response headers have already been sent.');
            
        self::$contentType = $val;
    }
    
    /**
     * Sets or returns the content encoding
     *
     * @param string $val The content encoding to be set
     * @return string|void If $val is left null, the current content
     *   encoding is returned
     */
    public static function Encoding($val = null)
    {
        // Are we setting or retrieving?
        if($val == null)
            return self::$charset;
        
        // Make sure the data wasnt sent already
        if(self::$outputSent)
            throw new OutputSentException('Cannot set content encoding because the response headers have already been sent.');
            
        self::$charset = $val;
    }
    
    /**
     * Sets a header $key to the given $value
     *
     * @param string $key The header key or name
     * @param string $value The header key's or name's value to be set
     * @return void
     */
    public static function SetHeader($key, $value)
    {
        // Make sure the data wasnt sent already
        if(self::$outputSent)
            throw new OutputSentException('Cannot set header because the response headers have already been sent.');
            
        $key = str_replace('_', '-', $key);
        if($key == 'Content-Type') 
        {
            if(preg_match('/^(.*);\w*charset\w*=\w*(.*)/', $value, $matches)) 
            {
                self::$contentType = $matches[1];
                self::$charset = $matches[2];
            } 
            else
                self::$contentType = $value;
        }
        else
            self::$headers[$key] = $value;
    }
    
    /**
     * Sets a cookies value
     *
     * @param string $name The cookie name
     * @param string $value The cookies value
     * @param int $expires The UNIX timestamp the cookie expires
     * @param string $path The cookie path
     * @return void
     */
    public static function SetCookie($name, $value, $expires, $path = '/')
    {
        // Make sure the data wasnt sent already
        if(self::$outputSent)
            throw new OutputSentException('Cannot set cookie because the response headers have already been sent.');
        
        self::$cookies[$name] = array(
            'value' => $value,
            'expires' => $expires,
            'path' => $path
        );
    }
    
    /**
     * Sets or returns the http protocol
     *
     * @param string $code The protocol to use (HTTP_10 | HTTP_11)
     * @return string|void If $code is null, the current protocol 
     *   is returned
     */
    public static function Protocol($code = null)
    {
        // Are we setting or retrieving?
        if(empty($code))
            return self::$protocol;
            
        // Make sure the data wasnt sent already
        if(self::$outputSent)
            throw new OutputSentException('Cannot set protocol because the response headers have already been sent.');
            
        // Make sure the protocol is valid!
        $code = strtoupper(trim($code));
        if($code !== self::HTTP_10 || $code !== self::HTTP_11)
            return false;
            
        self::$protocol = $code;
    }
    
    /**
     * This method sets a redirect header, and status code. When this
     * method is called, if the $wait param is greater then 1, headers 
     * will be sent.
     *
     * @param string $location The redirect URL. If a relative path
     *   is passed here, the site's URL will be appended
     * @param int $wait The wait time (in seconds) before the redirect 
     *   takes affect. If set to a non 0 value, the page will still be 
     *    rendered. Default is 0 seconds.
     * @param int $status The redirect status. 301 is moved permanently,
     *   and 307 is a temporary redirect. Default is 301.
     * @return void
     */
    public static function Redirect($location, $wait = 0, $status = 301)
    {
        // Make sure the data wasnt sent already
        if(self::$outputSent)
            throw new OutputSentException('Cannot set protocol because the response headers have already been sent.');
            
        // If we have a relative path, append the site url
        $location = trim($location);
        if(!preg_match('@^((mailto|ftp|http(s)?)://|www\.)@i', $location))
        {
            $location = Request::BaseUrl() .'/'. ltrim($location, '/');
        }
        
        // Reset all set data, and proccess the redirect immediately
        if($wait == 0)
        {
            self::$status = $status;
            self::$headers['Location'] = $location;
            self::$body = null;
            self::Send();
        }
        else
        {
            self::$status = $status;
            self::$headers['Refresh'] = $wait .';url='. $location;
        }
    }
    
    /**
     * Returns a bool of whether a redirect has been set or not
     *
     * @return bool
     */
    public static function HasRedirects()
    {
        return (isset(self::$headers['Location']) || isset(self::$headers['Refresh']));
    }
    
    /**
     * Removes all current redirects that are set
     *
     * @return void
     */
    public static function ClearRedirects()
    {
        if(isset(self::$headers['Location']))
            unset(self::$headers['Location']);
            
        if(isset(self::$headers['Refresh']))
            unset(self::$headers['Refresh']);
    }
    
    /**
     * Removes all current headers that are set
     *
     * @return void
     */
    public static function ClearHeaders()
    {
        self::$headers = array();
    }
    
    /**
     * Removes all current cookies that are modified
     *
     * @return void
     */
    public static function ClearCookies()
    {
        self::$cookies = array();
    }
    
    /**
     * Removes all current changes to the response, including the current
     * body buffer
     *
     * @return void
     */
    public static function Reset()
    {
        self::$headers = array();
        self::$cookies = array();
        self::$protocol = self::HTTP_11;
        self::$status = 200;
        self::$contentType = 'text/html';
        self::$charset = 'UTF-8';
        self::$body = null;
    }
    
    /**
     * Returns a bool based on whether the headers and output have been sent
     *
     * @return bool
     */
    public static function OutputSent()
    {
        return self::$outputSent;
    }
    
    
/*
| ---------------------------------------------------------------
| Internal Methods
| ---------------------------------------------------------------
|
*/

    /**
     * Sends all cookies
     *
     * @return void
     */
    protected static function SendCookies()
    {
        foreach(self::$cookies as $key => $values)
        {
            setcookie($key, $values['value'], $values['expires'], $values['path'], $_SERVER['HTTP_HOST']);
        }
    }
    
    /**
     * Sends a header
     *
     * @param string $name The name of the header
     * @param string $value The value of the header
     * @return void
     */
    protected static function SendHeader($name, $value = null)
    {
        // Make sure the headers havent been sent!
        if (!headers_sent()) 
        {
            if (is_null($value)) 
                header($name);
            else
                header("{$name}: {$value}");
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Sends the contents length
     *
     * @return void
     */
    protected static function SendContentLength()
    {
        // If we already have stuff in the buffer, append that lenght
        if(($len = ob_get_length()) != 0)
            self::$headers['Content-Length'] = $len + strlen(self::$body);
        else
            self::$headers['Content-Length'] = strlen(self::$body);
    }
    
    /**
     * Sends the content type
     *
     * @return void
     */
    protected static function SendContentType()
    {
        if (strpos(self::$contentType, 'text/') === 0)
            self::SetHeader('Content-Type', self::$contentType ."; charset=". self::$charset);
            
        elseif (self::$contentType === 'application/json')
            self::SetHeader('Content-Type', self::$contentType ."; charset=UTF-8");
            
        else
            self::SetHeader('Content-Type', self::$contentType);
    }
    
    /**
     * Echo's out the body contents
     *
     * @return void
     */
    protected static function SendBody()
    {
        echo self::$body;
    }
}

/**
 * Output Sent Exception, Thrown when headers have already been set, and a Repsonse method is called
 * @package     Core
 * @subpackage  Exceptions
 * @file        System/Core/Response.php
 */
class OutputSentException extends \Exception {}