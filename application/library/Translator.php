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
| Class: Translator
| ---------------------------------------------------------------
|
| This class can translate any 2 languages using mymemory translate
| API
|
*/
namespace Application\Library;

class Translator
{
    // Our delimiter for strings longer then 1000 characters
    protected $delim = '<--DELIM-->';
    
    // Users IP
    protected $ip;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct() 
    {
        // Load the URL helper
        load_class('Loader')->helper('Url');
        
        // Get the USERS ip so the site doesnt get in trouble for 2 many requests
        $this->ip = load_class('Input')->ip_address();
    }
    
/*
| ---------------------------------------------------------------
| Method: translate
| ---------------------------------------------------------------
|
| The main call method. This method returns the translated text
|
| @Param: $from - The 2 letter language ID (en, fr)  of the string
| @Param: $to - The 2 letter language ID (en, fr) to translate to
| @Param: $string - The string to be translated
| @Return (Mixed) Return the translated string, or false on failure
|
*/
    public function translate($from, $to, $string) 
    {
        // Remove html tags and @ characters!
        $string = stripslashes(strip_tags($string));
        $string = str_replace('@', '[at]', $string);
        
        // If string is longer then 1,000 characters, we have to make more then 1 request
        if(strlen($string) > 1000) 
        {
            $return = '';
            $newString = wordwrap($string, 1000, $this->delim);
            $newString = explode($this->delim, $newString);
            foreach($newString as $text) 
            {
                $r = $this->fetch_translation($from, $to, $text);
                if($r == false) return false;
                $return .= $r;
            }
        }
        else 
        {
            $return = $this->fetch_translation($from, $to, $string);
        }
        
        // Return
        return $return;
    }
    
/*
| ---------------------------------------------------------------
| Method: fetch_translation
| ---------------------------------------------------------------
|
| This method sends the request and gets the translated string.
|
*/
    protected function fetch_translation($from, $to, $string) 
    {
        // Build our URL, and get the contents of the page
        $url = 'http://mymemory.translated.net/api/get?q='. urlencode($string) .'&langpair='. urlencode($from.'|'.$to) .'&ip='. urlencode( $this->ip );
        $contents = getPageContents($url);
        if(empty($contents)) return false;
        
        // Decode our page
        $text = json_decode( $contents );
        
        // Return
        return $text->responseData->translatedText;
    }
}
 // EOF