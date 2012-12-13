<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Library/View.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    View
 * @contains    ViewNotFoundException
 * @contains    InvalidPageContents
 */
namespace Library;

/**
 * An individual view template class
 *
 * @author      Steven Wilson 
 * @package     Library
 */
class View
{
    /**
     * View contents as a string
     * @var string
     */
    protected $contents;
    
    /**
     * Assigned template variables and values
     * @var mixed[]
     */
    protected $variables = array();
    
    /**
     * Contructor
     *
     * @param string $string The file path to the template file, or the tempalte
     *   as a string
     * @param bool $isFile If set to true, $string becomes a filename, and is
     *   loaded. If false, $string is treated as the view's contents.
     *
     * @throws ViewNotFoundException if the view file cannot be located
     *
     * @return void
     */
    public function __construct($string, $isFile = true)
    {
        if($isFile && !file_exists($string))
            throw new ViewNotFoundException('Could not find view file "'. $string .'".');
        
        $this->contents = ($isFile) ? file_get_contents($string) : $string;
    }
    
    /**
     * Sets variables to be replaced in the view
     *
     * @param string|array $name Name of the variable to be set,
     *   or can be an array of key => value
     * @param mixed $value The value of the variable (not set if $name
     *   is an array)
     *
     * @return void
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
    
    /**
     * These method clears all the set variables for this view
     *
     * @return void
     */
    public function ClearVars()
    {
        $this->variables = array();
    }
    
    /**
     * Returns the view's contents, un-parsed
     *
     * @return string
     */
    public function GetContents()
    {
        return $this->contents;
    }
    
    /**
     * Sets the views contents
     *
     * @param string|\Library\View $contents The new contents of
     *   this view file. Must be a string, or an object extending
     *   this Class.
     *
     * @throws InvalidViewContents if the contents are not a string
     *   or a subclass of View
     *
     * @return void
     */
    public function SetContents($contents)
    {
        // Make sure out contents are valid
        if(!is_string($contents) && !(is_object($contents) && ($contents instanceof View)))
            throw new InvalidViewContents('Contents of the view must be a string, or an object extending the "View" class');
                
        $this->contents = $contents;
    }
    
    /**
     * These methods parses the view contents and returns the source
     *
     * @return string
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
    
    /**
     * These methods parses the view contents and returns the source
     *
     * @return string
     */
    public function __toString()
    {
        return $this->Render();
    }
}

// Class Exceptions //

/**
 * View Not Found Exception. Thrown when a view file cannot be found
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/View.php
 * @see         View
 */
class ViewNotFoundException extends \Exception {}

/**
 * Invalid View Contents. Thrown when the contents passed to a view, are
 *   not a string, or an extension of the \Library\View class
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/View.php
 * @see         View
 */
class InvalidViewContents extends \Exception {}