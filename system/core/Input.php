<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Input
| ---------------------------------------------------------------
|
| This class handles client side information such as input, cookies,
| $_POST vars, Ip address, browser etc etc.
| 
*/
namespace System\Core;

class Input
{

    // Cookie expire time
    protected $time;

    // Cookie path
    protected $cookie_path;

    // Cookie domain
    protected $cookie_domain;

    // Users IP address and Browser info
    protected $user_agent = FALSE;
    protected $ip_address = FALSE;

    // Array of tags and attributes
    protected $tagsArray = array();
    protected $attrArray = array();

    // Our tagging methods
    protected $tagsMethod = 0;
    protected $attrMethod = 0;

    // Use the xss cleaner
    protected $xssAuto = 1;

    // Blacklist of tags and attributes
    protected $tagBlacklist = array('applet', 'body', 'bgsound', 
        'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 
        'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml'
    );
    protected $attrBlacklist = array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/
    public function __construct()
    {
        // Set Cookie Defaults
        $this->time = ( time() + (60 * 60 * 24 * 365) );
        $this->cookie_path =  "/";
        $this->cookie_domain = rtrim($_SERVER['HTTP_HOST'], '/');
    }

/*
| ---------------------------------------------------------------
| Method: post()
| ---------------------------------------------------------------
|
| Returns a $_POST variable
|
| @Param: (String) $var - variable name to be returned
| @Param: (Bool) $xss - Check for XSS ?
| @Return (Mixed) Returns the value of $_POST[$var]
|
*/
    public function post($var, $xss = FALSE)
    {
        if(isset($_POST[$var]))
        {
            if($xss == FALSE)
            {
                return $_POST[$var];
            }
            else
            {
                return $this->clean($_POST[$var]);
            }
        }
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Returns a $_GET variable
|
| @Param: (String) $var - variable name to be returned
| @Param: (Bool) $xss - Check for XSS ?
| @Return (Mixed) Returns the value of $_GET[$var]
|
*/
    public function get($var, $xss = FALSE)
    {
        if(isset($_GET[$var]))
        {
            if($xss == FALSE)
            {
                return $_GET[$var];
            }
            else
            {
                return $this->clean($_GET[$var]);
            }
        }
        return FALSE;
    }

/*
| ---------------------------------------------------------------
| Method: cookie()
| ---------------------------------------------------------------
|
| Returns a $_COOKIE variable
|
| @Param: (String) $name - variable name to be returned
| @Param: (Bool) $xss - Check for XSS ?
| @Return (Mixed) Returns the value of $_COOKIE[$var]
|
*/
    public function cookie($name, $xss = FALSE)
    {
        if(isset($_COOKIE[$name]))
        {
            if($xss == FALSE)
            {
                return $_COOKIE[$name];
            }
            else
            {
                return $this->clean($_COOKIE[$name]);
            }
        }
        return FALSE;
    }

/*
| ---------------------------------------------------------------
| Method: set_cookie()
| ---------------------------------------------------------------
|
| Sets a cookie
|
| @Param: (String) $key - Name of the cookie
| @Param: (Mixed) $val - Value of the cookie
| @Param: (Int) $time - Cookie expire time in Unix Timestamp
| @Return (None)
|
*/
    function set_cookie($key, $val, $time = NULL)
    {
        if($time === NULL)
        {
            $time = $this->time;
        }
        // setcookie( $key, $val, $time, $this->cookie_path, $this->cookie_domain, false, true);
        setcookie( $key, $val, $time, $this->cookie_path );
    }
    
/*
| ---------------------------------------------------------------
| Method: is_ajax()
| ---------------------------------------------------------------
|
| @Return (Bool) Return's whether this is an AJAX request or no
|
*/
    public function is_ajax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

/*
| ---------------------------------------------------------------
| Method: user_agent()
| ---------------------------------------------------------------
|
| @Return (String) Returns the users browser info
|
*/
    public function user_agent()
    {
        if($this->user_agent == FALSE)
        {
            $this->user_agent = ( isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : FALSE );
        }
        return $this->user_agent;
    }

/*
| ---------------------------------------------------------------
| Method: ip_address()
| ---------------------------------------------------------------
|
| @Return (Mixed) Returns the users IP address, or 0.0.0.0 if
|   unable to determine. Order is in trust/use order top to bottom
|
*/
    public function ip_address()
    {
        // Return it if we already determined the IP
        if($this->ip_address === FALSE)
        {  
            // Check to see if the server has the IP address
            if(isset($_SERVER['HTTP_CLIENT_IP']) && $this->valid_ip($_SERVER['HTTP_CLIENT_IP']))
            {
                $this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
            }
            elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                // HTTP_X_FORWARDED_FOR can be an array og IPs!
                $ips = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach($ips as $ip_add) 
                {
                    if($this->valid_ip($ip_add))
                    {
                        $this->ip_address = $ip;
                        break;
                    }
                }
            }
            elseif(isset($_SERVER['HTTP_X_FORWARDED']) && $this->valid_ip($_SERVER['HTTP_X_FORWARDED']))
            {
                $this->ip_address = $_SERVER['HTTP_X_FORWARDED'];
            }
            elseif(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->valid_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            {
                $this->ip_address = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            }
            elseif(isset($_SERVER['HTTP_FORWARDED_FOR']) && $this->valid_ip($_SERVER['HTTP_FORWARDED_FOR']))
            {
                $this->ip_address = $_SERVER['HTTP_FORWARDED_FOR'];
            }
            elseif(isset($_SERVER['HTTP_FORWARDED']) && $this->valid_ip($_SERVER['HTTP_FORWARDED']))
            {
                $this->ip_address = $_SERVER['HTTP_FORWARDED'];
            }
            elseif(isset($_SERVER['HTTP_VIA']) && $this->valid_ip($_SERVER['HTTP_VIAD']))
            {
                $this->ip_address = $_SERVER['HTTP_VIA'];
            }
            elseif(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
            {
                $this->ip_address = $_SERVER['REMOTE_ADDR'];
            }

            // If we still have a FALSE IP address, then set to 0's
            if($this->ip_address === FALSE) $this->ip_address = '0.0.0.0';
        }
        return $this->ip_address;
    }
    
/*
| ---------------------------------------------------------------
| Method: valid_ip()
| ---------------------------------------------------------------
|
| Returns if the given IP address is a valid, Non-Private IP
|
| @Return (Bool)
|
*/
    public function valid_ip($ip)
    {
        // Trim the ip address
        $ip = trim($ip);
        if(!empty($ip) && ip2long($ip) != -1) 
        {
            $reserved_ips = array(
                array('0.0.0.0','2.255.255.255'),
                array('10.0.0.0','10.255.255.255'),
                array('127.0.0.0','127.255.255.255'),
                array('169.254.0.0','169.254.255.255'),
                array('172.16.0.0','172.31.255.255'),
                array('192.0.2.0','192.0.2.255'),
                array('192.168.0.0','192.168.255.255'),
                array('255.255.255.0','255.255.255.255')
            );

            foreach($reserved_ips as $r) 
            {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);
                if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
            }
            return true;
        }
        return false;
    }

/*
| ---------------------------------------------------------------
| PHP InputFilter
| ---------------------------------------------------------------
|
| NOTE: The below funtions where not created by myself, All i did
| was update the code and clean it up a bit. Here is the original 
| credits
|
| @project: PHP Input Filter
| @date: 10-05-2005
| @version: 1.2.2_php5
| @author: Daniel Morris
| @updated By: Steven Wilson
| @contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
| @copyright: Daniel Morris
| @email: dan@rootcube.com
| @license: GNU General Public License (GPL)
|
*/


/*
| ---------------------------------------------------------------
| Method: set_rules
| ---------------------------------------------------------------
|
| Sets the cleaning rules such as allowed tags etc.
|
| @param: (Array) $tagsArray - list of user-defined tags
| @param: (Array) $attrArray - list of user-defined attributes
| @param: (Int) $tagsMethod - 0 = allow just user-defined, 1= allow all but user-defined
| @param: (Int) $attrMethod - 0 = allow just user-defined, 1= allow all but user-defined
| @param: (Int) $xssAuto - 0 = only auto clean essentials, 1= allow clean blacklisted tags/attr
| @Return (None)
|
*/
    public function set_rules($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1) 
    {	
        // Count how many are in each for out loops
        $countTags = count($tagsArray);
        $countAttr = count($attrArray);
        
        // Loop through and lowercase all Tags
        for($i = 0; $i < $countTags; $i++)
        {
            $tagsArray[$i] = strtolower($tagsArray[$i]);
        }
        
        // Loop through and lowercase all attributes
        for($i = 0; $i < $countAttr; $i++)
        {
            $attrArray[$i] = strtolower($attrArray[$i]);
        }
        
        // Set our class variables
        $this->tagsArray = $tagsArray;
        $this->attrArray = $attrArray;
        $this->tagsMethod = $tagsMethod;
        $this->attrMethod = $attrMethod;
        $this->xssAuto = $xssAuto;
    }

/*
| ---------------------------------------------------------------
| Method: clean()
| ---------------------------------------------------------------
|
| Main call function. Used to clean user input
|
| @Param: (Mixed) $source - String or array to be cleaned
| @Return (Mixed) Returns the cleaned source of $source
|
*/
    public function clean($source) 
    {
        // If in array, clean each value
        if(is_array($source)) 
        {
            foreach($source as $key => $value)
            {
                if(is_string($value)) 
                {
                    // filter element for XSS and other 'bad' code etc.
                    $source[$key] = $this->remove($this->decode($value));
                }
            }
            return $source;
        } 
        elseif(is_string($source)) 
        {
            // filter element for XSS and other 'bad' code etc.
            return $this->remove($this->decode($source));
        } 
        return $source;
    }

/*
| ---------------------------------------------------------------
| Method: remove()
| ---------------------------------------------------------------
|
| Removes all unwanted tags and attributes
|
| @Param: (String) $source - String or array to be cleaned
| @Return (Mixed) Returns the cleaned source of $source
|
*/
    protected function remove($source) 
    {
        $loopCounter = 0;
        while($source != $this->filterTags($source)) 
        {
            $source = $this->filterTags($source);
            $loopCounter++;
        }
        return $source;
    }

/*
| ---------------------------------------------------------------
| Method: filterTags()
| ---------------------------------------------------------------
|
| Internal method to strip a string of certain tags
|
| @Param: (String) $source - String or array to be cleaned
| @Return (Mixed) Returns the cleaned source of $source
|
*/
    protected function filterTags($source) 
    {
        $preTag = NULL;
        $postTag = $source;
        
        // find initial tag's position
        $tagOpen_start = strpos($source, '<');
        
        // interate through string until no tags left
        while($tagOpen_start !== FALSE) 
        {
            // process tag interatively
            $preTag .= substr($postTag, 0, $tagOpen_start);
            $postTag = substr($postTag, $tagOpen_start);
            $fromTagOpen = substr($postTag, 1);
            $tagOpen_end = strpos($fromTagOpen, '>');
            if($tagOpen_end === false)
            {
                break;
            }
            
            // next start of tag (for nested tag assessment)
            $tagOpen_nested = strpos($fromTagOpen, '<');
            if(($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end)) 
            {
                $preTag .= substr($postTag, 0, ($tagOpen_nested + 1));
                $postTag = substr($postTag, ($tagOpen_nested + 1));
                $tagOpen_start = strpos($postTag, '<');
                continue;
            } 
            $tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start + 1);
            $currentTag = substr($fromTagOpen, 0, $tagOpen_end);
            $tagLength = strlen($currentTag);
            if(!$tagOpen_end) 
            {
                $preTag .= $postTag;
                $tagOpen_start = strpos($postTag, '<');			
            }
            
            // iterate through tag finding attribute pairs - setup
            $tagLeft = $currentTag;
            $attrSet = array();
            $currentSpace = strpos($tagLeft, ' ');
            
            // is end tag
            if(substr($currentTag, 0, 1) == "/") 
            {
                $isCloseTag = TRUE;
                list($tagName) = explode(' ', $currentTag);
                $tagName = substr($tagName, 1);
            } 
            
            // is start tag
            else 
            {
                $isCloseTag = FALSE;
                list($tagName) = explode(' ', $currentTag);
            }	

            // excludes all "non-regular" tagnames OR no tagname OR remove if xssauto is on and tag is blacklisted
            if((!preg_match("/^[a-z][a-z0-9]*$/i", $tagName)) || (!$tagName) || ((in_array(strtolower($tagName), $this->tagBlacklist)) && ($this->xssAuto))) 
            { 				
                $postTag = substr($postTag, ($tagLength + 2));
                $tagOpen_start = strpos($postTag, '<');
                continue;
            }
            
            // this while is needed to support attribute values with spaces in!
            while($currentSpace !== FALSE) 
            {
                $fromSpace = substr($tagLeft, ($currentSpace+1));
                $nextSpace = strpos($fromSpace, ' ');
                $openQuotes = strpos($fromSpace, '"');
                $closeQuotes = strpos(substr($fromSpace, ($openQuotes+1)), '"') + $openQuotes + 1;
                
                // another equals exists
                if(strpos($fromSpace, '=') !== FALSE) 
                {
                    // opening and closing quotes exists
                    if(($openQuotes !== FALSE) && (strpos(substr($fromSpace, ($openQuotes+1)), '"') !== FALSE))
                    {
                        $attr = substr($fromSpace, 0, ($closeQuotes+1));
                    }
                    
                    // one or neither exist
                    else 
                    {
                        $attr = substr($fromSpace, 0, $nextSpace);
                    }
                }
                
                // no more equals exist
                else
                {
                    $attr = substr($fromSpace, 0, $nextSpace);
                }
                
                // last attr pair
                if(!$attr) 
                {
                    $attr = $fromSpace;
                }
                
                // add to attribute pairs array
                $attrSet[] = $attr;
                
                // next inc
                $tagLeft = substr($fromSpace, strlen($attr));
                $currentSpace = strpos($tagLeft, ' ');
            }
            
            // appears in array specified by user
            $tagFound = in_array(strtolower($tagName), $this->tagsArray);

            // remove this tag on condition			
            if((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod)) 
            {
                // reconstruct tag with allowed attributes
                if(!$isCloseTag) 
                {
                    $attrSet = $this->filterAttr($attrSet);
                    $preTag .= '<' . $tagName;
                    for($i = 0; $i < count($attrSet); $i++)
                    {
                        $preTag .= ' ' . $attrSet[$i];
                    }
                    
                    // reformat single tags to XHTML
                    if(strpos($fromTagOpen, "</" . $tagName))
                    {
                        $preTag .= '>';
                    }
                    else 
                    {
                        $preTag .= ' />';
                    }
                } 
                
                // just the tagname
                else 
                {
                    $preTag .= '</' . $tagName . '>';
                }
            }
            
            // find next tag's start
            $postTag = substr($postTag, ($tagLength + 2));
            $tagOpen_start = strpos($postTag, '<');			
        }
        
        // append any code after end of tags
        $preTag .= $postTag;
        return $preTag;
    }

/*
| ---------------------------------------------------------------
| Method: filterAttr()
| ---------------------------------------------------------------
|
| Internal method to strip a tag of certain attributes
|
| @Param: (String) $source - String or array to be cleaned
| @Return (Mixed) Returns the cleaned source of $source
|
*/
    protected function filterAttr($attrSet) 
    {	
        $newSet = array();
        
        // process attributes
        for($i = 0; $i <count($attrSet); $i++) 
        {
            // skip blank spaces in tag
            if(!$attrSet[$i])
            {
                continue; 
            }
            
            // split into attr name and value
            $attrSubSet = explode('=', trim($attrSet[$i]));
            list($attrSubSet[0]) = explode(' ', $attrSubSet[0]);
            
            // removes all "non-regular" attr names AND also attr blacklisted
            if ((!preg_match("/^[a-z]*$/i", $attrSubSet[0])) || (($this->xssAuto) && ((in_array(strtolower($attrSubSet[0]), $this->attrBlacklist)) || (substr($attrSubSet[0], 0, 2) == 'on'))))
            {
                continue;
            }
            
            // xss attr value filtering
            if($attrSubSet[1]) 
            {
                // strips unicode, hex, etc
                $attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);
                
                // strip normal newline within attr value
                $attrSubSet[1] = preg_replace('/\s+/', '', $attrSubSet[1]);
                
                // strip double quotes
                $attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);
                
                // [requested feature] convert single quotes from either side to doubles (Single quotes shouldn't be used to pad attr value)
                if ((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'"))
                {
                    $attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));
                }
                
                // strip slashes
                $attrSubSet[1] = stripslashes($attrSubSet[1]);
            }
            
            // auto strip attr's with "javascript:
            if(	
                ((strpos(strtolower($attrSubSet[1]), 'expression') !== false) && (strtolower($attrSubSet[0]) == 'style')) 
                || (strpos(strtolower($attrSubSet[1]), 'javascript:') !== false)
                || (strpos(strtolower($attrSubSet[1]), 'behaviour:') !== false) 
                || (strpos(strtolower($attrSubSet[1]), 'vbscript:') !== false) 
                || (strpos(strtolower($attrSubSet[1]), 'mocha:') !== false)
                || (strpos(strtolower($attrSubSet[1]), 'livescript:') !== false) 
            ) continue;
            
            // if matches user defined array
            $attrFound = in_array(strtolower($attrSubSet[0]), $this->attrArray);
            
            // keep this attr on condition
            if((!$attrFound && $this->attrMethod) || ($attrFound && !$this->attrMethod)) 
            {
                // attr has value
                if($attrSubSet[1])
                {
                    $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
                }
                
                // attr has decimal zero as value
                elseif($attrSubSet[1] == "0")
                {
                    $newSet[] = $attrSubSet[0] . '="0"';
                }
                
                // reformat single attributes to XHTML
                else
                {
                    $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[0] . '"';
                }
            }	
        }
        return $newSet;
    }

/*
| ---------------------------------------------------------------
| Method: decode()
| ---------------------------------------------------------------
|
| Converts to plain text
|
| @Param: (String) $source - String to be converted
| @Return (Mixed) Returns the cleaned source of $source
|
*/
    protected function decode($source) 
    {
        $source = html_entity_decode($source, ENT_QUOTES, "ISO-8859-1");
        $source = preg_replace('/&#(\d+);/me',"chr(\\1)", $source);
        $source = preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)", $source);
        return $source;
    }
}
// EOF 