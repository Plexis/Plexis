<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Library/Template.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Template
 */
namespace Library;

// Import core classes into scope
use \Core\Request;

/**
 * Template Engine for the CMS
 *
 * @author      Steven Wilson 
 * @package     Library
 */
class Template
{
    /**
     * The current body view contents
     * @var string
     */
    protected static $buffer = null;
    
    /**
     * The root path to the theme
     * @var string
     */
    protected static $themePath;
    
    /**
     * The complete http path to the theme root
     * @var string
     */
    protected static $themeUrl;
    
    /**
     * Theme xml config object
     * @var SimpleXMLElement
     */
    protected static $themeConfig;
    
    /**
     * Array of template messages
     * @var array[] ('level', 'message')
     */
    protected static $messages = array();
    
    /**
     * Variables to be parsed in the layout view
     * @var mixed[]
     */
    protected static $variables = array();
    
    /**
     * Renders the current body contents into the template layout
     *
     * @param bool $return When set to true, final rendered template
     *   is returned instead of echo'ing it out.
     * @param bool $loadLayout Load the layout?
     *
     * @return string|void Only returns the parsed page if $return is true
     */
    public static function Render($return = false, $loadLayout = true)
    {
        // First, load the template xml config file
        if(empty(self::$themeConfig)) self::LoadThemeConfig();
        
        // Load contents and parse the layout file
        if($loadLayout)
        {
            // Load the layout, and parse it
            $Layout = new View(self::$themePath . DS . 'layout.tpl');
            foreach(self::$variables as $k => $v)
                $Layout->Set($k, $v);
            $Layout->Set('CONTENTS', self::$buffer);
            $Layout->Set('GLOBAL_MESSAGES', self::ParseGlobalMessages());
            $c = $Layout->Render();
        }
        else
            $c = self::$buffer;
        
        if($return)
            return $c;
        else
            echo $c;
    }
    
    /**
     * Adds more to contents to be added into the contents section of
     * the final rendered template. Takes unlimited number of params
     *
     * @params string|\Library\View
     *
     * @return void
     */
    public static function Add()
    {
        $parts = func_get_args();
        foreach($parts as $contents)
        {
            // Make sure out contents are valid
            if(!is_string($contents) && !(is_object($contents) && ($contents instanceof View)))
                throw new InvalidPageContents('Page contents must be a string, or an object extending the "View" class');
                
            self::$buffer .= (string) $contents;
        }
    }
    
    /**
     * Sets variables to be parsed in the layout
     *
     * @param string $name Name of the variable
     * @param mixed $value The value of the variable
     *
     * @return void
     */
    public static function SetVar($name, $value)
    {
        if(is_array($name))
        {
            foreach($name as $key => $val)
            {
                self::$variables[$key] = $val;
            }
        }
        else
        {
            self::$variables[$name] = $value;
        }
    }
    
    /**
     * Sets the path to the theme, where the layout.tpl is located
     *
     * @param string $path The full path to the theme
     *
     * @throws InvalidThemePathException If the theme config cannot be found
     *
     * @return void
     */
    public static function SetThemePath($path)
    {
        // Make sure the path exists!
        if(!file_exists($path))
            throw new InvalidThemePathException('Invalid theme path "'. $path .'"');
        
        // Set theme path
        self::$themePath = $path;
        
        // Build the HTTP url to the theme's root folder
        $path = str_replace(ROOT . DS, '', $path);
        self::$themeUrl = Request::BaseUrl() .'/'. str_replace(DS, '/', $path);
    }
    
    /**
     * Adds a global message to be parsed into the template
     *
     * @param string $lvl The message level (error, warning, info etc)
     * @param string $message The message to be displayed within the rendered template
     *
     * @return void
     */
    public static function Message($lvl, $message)
    {
        self::$messages[] = array($lvl, $message);
    }
    
    /**
     * Clears the current output buffer of the template
     *
     * @return void
     */
    public static function ClearBuffer()
    {
        self::$buffer = null;
    }
    
    /**
     * Parse the global messages for the template renderer
     *
     * @return string The parsed global message contents
     */
    protected static function ParseGlobalMessages()
    {
        // Load the global_messages view
        try {
            $View = new View( path(self::$themePath, 'views', 'message.tpl') );
        }
        catch( ViewNotFoundException $e ) {
            throw $e;
        }
        
        // Loop through and add each message to the buffer
        $buffer = '';
        $size = sizeof(self::$messages);
        foreach(self::$messages as $k => $m)
        {
            $View->Set('level', $m[0]);
            $View->Set('message', $m[1]);
            $buffer .= $View->Render();
            if($k+1 != $size) 
                $buffer .= PHP_EOL;
        }
        
        return $buffer;
    }
    
    /**
     * Internal method for loading the theme's config xml file
     *
     * @throws ThemeNotSetException if the theme isnt set before rendering
     * @throws MissingThemeConfigException if the theme is missing its theme
     * config file (theme.xml)
     *
     * @return void
     */
    protected static function LoadThemeConfig()
    {
        // Make sure a theme is set
        if(empty(self::$themePath))
            throw new ThemeNotSetException('No theme selected!');
        
        // Make sure the theme config file exists
        $file = path(self::$themePath, 'theme.xml');
        if(!file_exists($file))
            throw new MissingThemeConfigException('Unable to load theme config file "'. $file .'"');
        
        // Load the config as an Xml Object
        self::$themeConfig = simplexml_load_file($file);
    }
    
/*
| ---------------------------------------------------------------
| Template Header Building Functions
| ---------------------------------------------------------------
*/

    /**
     * AddCssFile
     *
     * @param string $location The http location of the file
     *
     * @return void
     */
    public static function AddCssFile($location)
    {
    
    }
    
    /**
     * AddJsFile
     *
     * @param string $location The http location of the file
     *
     * @return void
     */
    public static function AddJsFile($location)
    {
    
    }
    
    /**
     * Sets the page title
     *
     * @param string $title The title of the page
     *
     * @return void
     */
    public static function PageTitle($title)
    {
    
    }
    
    /**
     * _buildHeader()
     *
     * @return void
     */
    protected static function _buildHeader()
    {
    
    }

}

// Exceptions //

/**
 * Thrown by the Template Class when the render method is called, but not theme path was set
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Template.php
 * @see         Template::Render()
 */
class ThemeNotSetException extends \ApplicationError {}

/**
 * Thrown by the Template Class if the theme path provided is an invalid path
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Template.php
 * @see         Template::SetThemePath()
 */
class InvalidThemePathException extends \ApplicationError {}

/**
 * Thrown by the Template Class if the contents provided are not a string, or subclass of the View method
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Template.php
 * @see         Template::Add()
 */
class InvalidPageContents extends \ApplicationError {}

/**
 * Thrown by the Template Class if the theme is missing its config file
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Template.php
 * @see         Template::SetThemePath()
 */
class MissingThemeConfigException extends \ApplicationError {}