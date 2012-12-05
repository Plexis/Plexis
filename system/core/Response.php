<?php

namespace Core;

class Response
{
    // Http protocol constants
    const HTTP_10 = 'HTTP/1.0';
    const HTTP_11 = 'HTTP/1.1';
    
    // Header data
    protected static $status = 200;
    protected static $protocol = self::HTTP_11;
    protected static $charset = 'UTF-8';
    protected static $contentType = 'text/html';
    protected static $headers = array();
    protected static $cookies = array();
    protected static $body = null;
    protected static $cacheDirectives = array();
	protected static $outputSent = false;
    
    // Array of status codes
    protected static $statusCodes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out'
    );
    
/*
| ---------------------------------------------------------------
| Method: Send()
| ---------------------------------------------------------------
|
| This method takes all the response headers, cookies, and current
| buffered contents, and sends them back to the client. After this
| methid is called, any output will most likely cause a content lenght
| error for our client.
|
| @Return (None)
|
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
    
/*
| ---------------------------------------------------------------
| Method: Body()
| ---------------------------------------------------------------
|
| This method sets or returns the body of the response, based on
| if a variable is passed setting the contents or not.
|
| @Param (String) $content - The body contents, leave null if retrieving 
|	current set contents
| @Return (String | None)
|
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
    
/*
| ---------------------------------------------------------------
| Method: AppendBody()
| ---------------------------------------------------------------
|
| This method appends data to the current body
|
| @Param (String) $content - The body contents, leave null if retrieving 
|	current set contents
| @Return (String | None)
|
*/
    public static function AppendBody($content)
    {
		// Make sure the data wasnt sent already
		if(self::$outputSent)
			throw new OutputSentException('Cannot append body contents because the response headers have already been sent.');
			
        self::$body .= (string) $content;
    }
    
/*
| ---------------------------------------------------------------
| Method: StatusCode()
| ---------------------------------------------------------------
|
| This method sets or returns the status code.
|
| @Param (Int) $code - The status code to be set
| @Return (Int | None) returns the current code if $code is null
|
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
    
/*
| ---------------------------------------------------------------
| Method: ContentType()
| ---------------------------------------------------------------
|
| This method sets or returns the content type.
|
| @Param (String) $val - The content type to be set
| @Return (String | None) returns the current content type if $val is null
|
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
    
/*
| ---------------------------------------------------------------
| Method: Encoding()
| ---------------------------------------------------------------
|
| This method sets or returns the content encoding.
|
| @Param (String) $code - The content encoding to be set
| @Return (String | None) returns the current content encoding if $val is null
|
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
    
/*
| ---------------------------------------------------------------
| Method: SetHeader()
| ---------------------------------------------------------------
|
| This method sets a header $key to the given $value
|
| @Param (String) $key - The header key
| @Param (String) $value - The header key value
| @Return (None)
|
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
    
/*
| ---------------------------------------------------------------
| Method: SetCookie()
| ---------------------------------------------------------------
|
| This method sets a cookie $name to the given $value
|
| @Param (String) $name - The cookie name
| @Param (String) $value - The cookie value
| @Param (Int) $expires - The UNIX timestamp the cookie expires
| @Param (String) $path - The cookie path
| @Return (None)
|
*/
    public static function SetCookie($name, $value, $expires, $path = '/')
    {
		// Make sure the data wasnt sent already
		if(self::$outputSent)
			throw new OutputSentException('Cannot set cookie because the response headers have already been sent.');
			
        $_COOKIE[$name] = $value;
        self::$cookies[$name] = array(
            'value' => $value,
            'expires' => $expires,
			'path' => $path
        );
    }
	
/*
| ---------------------------------------------------------------
| Method: Protocol()
| ---------------------------------------------------------------
|
| This method sets or returns the http protocol.
|
| @Param (String) $code - The protocol to be set
| @Return (String | None) returns the current protocol if $code is null
|
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
    
/*
| ---------------------------------------------------------------
| Method: Redirect()
| ---------------------------------------------------------------
|
| This method sets a redirect header, and status code
|
| @Param (String) $location - The redirect url
| @Param (Int) $status - The status code to be set
| @Return (None)
|
*/
    public static function Redirect($location, $status = 301)
    {
        self::status($status);
        self::SetHeader('Location', $location);
    }
    
/*
| ---------------------------------------------------------------
| Method: HasRedirects()
| ---------------------------------------------------------------
|
| Returns a bool of whether a redirect has been set or not
|
| @Return (Bool)
|
*/
    public static function HasRedirects()
    {
        return isset(self::$headers['Location']);
    }
    
/*
| ---------------------------------------------------------------
| Method: ClearRedirects()
| ---------------------------------------------------------------
|
| Removes all current redirects that are set
|
| @Return (None)
|
*/
    public static function ClearRedirects()
    {
        if(isset(self::$headers['Location']))
            unset(self::$headers['Location']);
    }
    
/*
| ---------------------------------------------------------------
| Method: ClearHeaders()
| ---------------------------------------------------------------
|
| Removes all current headers that are set
|
| @Return (None)
|
*/
    public static function ClearHeaders()
    {
        self::$headers = array();
    }
    
/*
| ---------------------------------------------------------------
| Method: ClearCookies()
| ---------------------------------------------------------------
|
| Removes all current cookies that are modified
|
| @Return (None)
|
*/
    public static function ClearCookies()
    {
        self::$cookies = array();
    }
	
/*
| ---------------------------------------------------------------
| Method: OutputSent()
| ---------------------------------------------------------------
|
| Returns a bool based on whether the headers and output have been sent
|
| @Return (Bool)
|
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
    protected static function SendCookies()
    {
        foreach(self::$cookies as $key => $values)
        {
            setcookie($key, $values['value'], $values['expires'], $values['path']);
        }
    }
    
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
    
    protected static function SendContentLength()
    {
		// If we already have stuff in the buffer, append that lenght
        if(($len = ob_get_length()) != 0)
            self::$headers['Content-Length'] = $len + strlen(self::$body);
        else
            self::$headers['Content-Length'] = strlen(self::$body);
    }
    
    protected static function SendContentType()
    {
        if (strpos(self::$contentType, 'text/') === 0)
            self::SetHeader('Content-Type', self::$contentType ."; charset=". self::$charset);
            
        elseif (self::$contentType === 'application/json')
            self::SetHeader('Content-Type', self::$contentType ."; charset=UTF-8");
            
        else
            self::SetHeader('Content-Type', self::$contentType);
    }
    
    protected static function SendBody()
    {
        echo self::$body;
    }
}

class OutputSentException extends \Exception {}