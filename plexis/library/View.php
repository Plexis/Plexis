<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Copyright:    Copyright (c) 2011-2012, Plexis Dev Team
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: View
| ---------------------------------------------------------------
|
| An individual view template class
|
*/
namespace Library;

class View
{
    // View contents as a string
    protected $contents;
    
    // Assigned template variables and values
    protected $variables = array();
    
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
| @Param: (String) $path - The full path to the view file
|
*/ 
    public function __construct($string, $isFile = true)
    {
        if($isFile && !file_exists($string))
            throw new ViewNotFoundException('Could not find view file "'. $string .'".');
        
        $this->contents = ($isFile) ? file_get_contents($string) : $string;
    }
    
/*
| ---------------------------------------------------------------
| Method: Set()
| ---------------------------------------------------------------
|
| This method sets variables to be replace in the view
|
| @Param: (String | Array) $name - Name of the variable to be set,
|   or can be an array of key => value
| @Param: (Mixed) $value - The value of the variable (not set if $name
|   is an array)
| @Return (None)
|
*/
    public function Set($name, $value = null)
    {
        if(is_array($name))
        {
            foreach($name as $key => $val)
            {
                $this->variables[$key] = $val;
            }
        }
        else
        {
            $this->variables[$name] = $value;
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: ClearVars()
| ---------------------------------------------------------------
|
| These method clears all the set variables for this view
|
| @Return (None)
|
*/
    public function ClearVars()
    {
        $this->variables = array();
    }
    
/*
| ---------------------------------------------------------------
| Method: GetContents()
| ---------------------------------------------------------------
|
| These method returns the view's contents, un-parsed
|
| @Return (String)
|
*/
    public function GetContents()
    {
        return $this->contents;
    }
    
/*
| ---------------------------------------------------------------
| Method: SetContents()
| ---------------------------------------------------------------
|
| These method sets the views contents
|
| @Param: (String | Object(View)) $contents - The new contents of
|   this view file. Must be a string, or an object extending the 
|   this Class.
| @Return (None)
|
*/
    public function SetContents($contents)
    {
        // Make sure out contents are valid
        if(!is_string($contents) && !(is_object($contents) && ($contents instanceof View)))
            throw new InvalidViewContents('Contents of the view must be a string, or an object extending the "View" class');
                
        $this->contents = $contents;
    }
    
/*
| ---------------------------------------------------------------
| Methods: Render() & __toString()
| ---------------------------------------------------------------
|
| These methods parses the view contents and returns the source
|
| @Return (String)
|
*/
    public function Render()
    {
        if(!empty($this->variables))
        {
            // Extract the class variables so $this->variables[ $var ] becomes $var
            extract($this->variables);
            
            // Start contents capture
            ob_start();
            
            // Eval the source so we can process the php tags in the view correctly
            eval('?>'. Parser::Parse($this->contents, $this->variables));
            
            // Capture the completed source, and return it
            return ob_get_clean();
        }
        
        return $this->contents;
    }
    
    public function __toString()
    {
        return $this->Render();
    }
}

// Class Exceptions //

class ViewNotFoundException extends \ApplicationError {}

class InvalidViewContents extends \ApplicationError {}