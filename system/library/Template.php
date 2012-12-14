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
use \Core\Benchmark;
use \Core\Config;
use \Core\Request;
use \Core\Response;

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
     * The root path to the themes folder
     * @var string
     */
    protected static $themePath;
    
    /**
     * The selected theme name
     * @var string
     */
    protected static $themeName;
    
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
     * The page title for the title tag (appended after server name)
     * @var string
     */
    protected static $pageTitle;
    
    /**
     * An array of lines to be injected into the layout head tags
     * @var string[]
     */
    protected static $headers = array();
    
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
     * Javascript Variables to be added in the header
     * @var mixed[]
     */
    protected static $jsVars = array();
    
    /**
     * Renders the current body contents into the template layout
     *
     * If the $return param is false, the Response object will be called
     * internally, and the Reponse headers and content will be sent to the
     * browser.
     *
     * @param bool $return When set to true, final rendered template
     *   is returned instead of sending the response.
     * @param bool $loadLayout Load the layout?
     *
     * @return string|void Only returns the parsed page if $return is true
     */
    public static function Render($return = false, $loadLayout = true)
    {
        // default
        $contents = null;
        
        // Load contents and parse the layout file
        if($loadLayout)
        {
            // First, load the template xml config file
            if(empty(self::$themeConfig)) 
                self::LoadThemeConfig();
        
            // Load the layout, and parse it
            $contents = self::RenderLayout();
        }
        else
        {
            $contents = self::$buffer;
        }
        
        // Return contents if requested
        if($return)
            return $contents;
        
        // Send the response
        Response::Body($contents);
        Response::Send();
    }
    
    /**
     * Loads a view file from the template's module view folder.
     *
     * @param string $module The name of the module (where the view is located)
     *   If the $name parameter is false, then this param becomes becomes the 
     *   partial view name, and a partial view is loaded rather then a full
     *   module view.
     * @param string $name The name of the view file (no extension). If set
     *   to false, then the $module param becomes the view name, and a
     *   template partial view is loaded instead.
     *
     * @throws ViewNotFoundException if the template does not have the view
     *   file for the specified module
     *
     * @return \Library\View
     */
    public static function LoadView($module, $name = false)
    {
        // Build path... Are we loading a partial or module view?
        $path = (!$name)
            ? path(self::$themePath, self::$themeName, 'views', $module .'.tpl')
            : path(self::$themePath, self::$themeName, 'views', strtolower($module), $name .'.tpl');
        
        // Try and load the view
        return new View($path);
    }
    
    /**
     * Adds more to contents to be added into the contents section of
     * the final rendered template.
     *
     * @param string|\Library\View $contents The contents to add to the template body
     * @param string|bool $css The css file to be loaded for this view
     * @param string|bool $js The javascript file to be loaded for this view. When the
     *   Template::Render() method is called, a view JS file will be located automatically.
     *
     * @return void
     *
     * @todo Finish the $css and $js variables
     */
    public static function Add($contents, $css = false, $js = false)
    {
        // Make sure out contents are valid
        if(!is_string($contents) && !(is_object($contents) && ($contents instanceof View)))
            throw new InvalidPageContents('Page contents must be a string, or an object extending the "View" class');
        
        // Render view contents
        if($contents instanceof View)
            $contents = $contents->Render();
            
        // Append to buffer
        self::$buffer .= $contents;
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
     * Sets javascript variables to be added in the head tags
     *
     * @param string $name Name of the variable
     * @param mixed $value The value of the variable
     *
     * @return void
     */
    public static function SetJsVar($name, $value)
    {
        if(is_array($name))
        {
            foreach($name as $key => $val)
            {
                self::$jsVars[$key] = $val;
            }
        }
        else
        {
            self::$jsVars[$name] = $value;
        }
    }
    
    /**
     * Sets the path to the theme folder
     *
     * @param string $path The full path to the theme folder
     * @param string $name The theme name. Set only if you want to also define
     *   the theme name as well as the path
     *
     * @throws InvalidThemePathException If the theme config cannot be found
     *
     * @return void
     */
    public static function SetThemePath($path, $name = false)
    {
        // Make sure the path exists!
        if(!is_dir($path))
            throw new InvalidThemePathException('Invalid theme path "'. $path .'"');
        
        // Set theme path
        self::$themePath = $path;
        
        // Set the theme name if possible
        if($name != false)
            self::SetTheme($name);
    }
    
    /**
     * Sets the name of the theme to render, where the layout.tpl is located
     *
     * @param string $name The theme name
     *
     * @throws InvalidThemePathException If the theme doesnt exist in the theme path
     *
     * @return void
     */
    public static function SetTheme($name)
    {
        // Make sure the path exists!
        $path = path(self::$themePath, $name, 'theme.xml');
        if(empty(self::$themePath) || !file_exists($path))
            throw new InvalidThemePathException('Cannot find theme config file! "'. $path .'"');
        
        
        // Build the HTTP url to the theme's root folder
        self::$themeName = $name;
        $path = str_replace(ROOT . DS, '', dirname($path));
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
    public static function ClearContents()
    {
        self::$buffer = null;
    }
    
    
/*
| ---------------------------------------------------------------
| Template Header Building Methods
| ---------------------------------------------------------------
*/

    /**
     * Appends the header adding a css tag
     *
     * @param string $location The http location of the file
     *
     * @return void
     */
    public static function AddCssFile($location)
    {
        $location = trim($location);
        
        // If we dont have a complete url, we need to determine if the css
        // file is a plexis, or template file
        if(!preg_match('@^((ftp|http(s)?)://|www\.)@i', $location))
        {
            $parts = explode('/', $location);
            $file = path(self::$themePath, $parts);
            
            // Are we handling a template or plexis asset?
            $location = (file_exists($file)) ? self::$themeUrl .'/'. ltrim($location, '/') : Request::BaseUrl() .'/'. ltrim($location, '/');
        }
        self::$headers[] = '<link rel="stylesheet" type="text/css" href="'. $location .'"/>';
    }
    
    /**
     * Appends the header adding a script tag
     *
     * @param string $location The http location of the file
     *
     * @return void
     */
    public static function AddJsFile($location)
    {
        $location = trim($location);
        
        // If we dont have a complete url, we need to determine if the css
        // file is a plexis, or template file
        if(!preg_match('@^((ftp|http(s)?)://|www\.)@i', $location))
        {
            $parts = explode('/', $location);
            $file = path(self::$themePath, $parts);
            
            // Are we handling a template or plexis asset?
            $location = (file_exists($file)) ? self::$themeUrl .'/'. ltrim($location, '/') : Request::BaseUrl() .'/'. ltrim($location, '/');
        }
        self::$headers[] = '<script type="text/javascript" src="'. $location .'"></script>';
    }
    
    /**
     * Sets the page title (After server title)
     *
     * @param string $title The title of the page
     *
     * @return void
     */
    public static function PageTitle($title)
    {
        self::$pageTitle = $title;
    }
    
    /**
     * Adds a new line of code to the head tags
     *
     * @param string $line The line to add
     *
     * @return void
     */
    public static function AppendHeader($line)
    {
        self::$header[] = $line;
    }
    
/*
| ---------------------------------------------------------------
| Internal Methods
| ---------------------------------------------------------------
*/
    
    /**
     * Builds the plexis header
     *
     * @return string The rendered header data
     */
    protected static function BuildHeader()
    {
        // Convert our JS vars into a string :)
        $string = 
        "        var Plexis = {
            Url : '". SITE_URL ."',
            TemplateUrl : '". self::$themeUrl ."',
            Debugging : false,
            RealmId : 1,
        }\n";
        foreach(self::$jsVars as $key => $val)
        {
            // Format the var based on type
            $val = (is_numeric($val)) ? $val : '"'. $val .'"';
            $string .= "        var ". $key ." = ". $val .";\n";
        }
        
        // Build Basic Headers
        $base = Request::BaseUrl();
        $headers = array(
            '<!-- Basic Headings -->',
            '<title>'. Config::GetVar('site_title', 'Plexis') .'</title>',
            '<meta name="keywords" content="'. Config::GetVar('keywords', 'Plexis') .'"/>',
            '<meta name="description" content="'. Config::GetVar('description', 'Plexis') .'"/>',
            '<meta name="generator" content="Plexis"/>',
            '', // Add Whitespace
            '<!-- Content type, And cache control -->',
            '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>',
            '<meta http-equiv="Cache-Control" content="no-cache"/>',
            '<meta http-equiv="Expires" content="-1"/>',
            '', // Add Whitespace
            '<!-- Include jQuery Scripts -->',
            '<script type="text/javascript" src="'. $base .'/assets/js/jquery.js"></script>',
            '<script type="text/javascript" src="'. $base .'/assets/js/jquery-ui.js"></script>',
            '<script type="text/javascript" src="'. $base .'/assets/js/jquery.validate.js"></script>',
            '', // Add Whitespace
            '<!-- Define Global Vars and Include Plexis Static JS Scripts -->',
            "<script type=\"text/javascript\">\n". rtrim($string) ."\n    </script>",
            '<script type="text/javascript" src="'. $base .'/assets/js/plexis.js"></script>',
            '' // Add Whitespace
        );
        
        // Merge user added headers
        if(!empty(self::$headers))
        {
            $headers[] = '';
            $headers[] = '<!-- Controller Added -->';
            $headers = array_merge($headers, self::$headers);
        }
        
        return implode("\n    ", $headers);
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
            $View = new View( path(self::$themePath, self::$themeName, 'views', 'message.tpl') );
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
        $file = path(self::$themePath, self::$themeName, 'theme.xml');
        if(!file_exists($file))
            throw new MissingThemeConfigException('Unable to load theme config file "'. $file .'"');
        
        // Load the config as an Xml Object
        self::$themeConfig = simplexml_load_file($file);
    }
    
    /**
     * Internal method for parsing template tags and rendering the layout
     *
     * @return string The parsed contents
     */
    protected static function RenderLayout()
    {
        // Get layout contents
        $path = path(self::$themePath, self::$themeName, 'views', 'layout.tpl');
        $contents = file_get_contents( $path );
        
        // Parse plexis tags (temporary till i input a better method)
        $contents = str_ireplace('{plexis::head}', trim(self::BuildHeader()), $contents);
        $contents = str_ireplace('{plexis::contents}', self::$buffer, $contents);
        $contents = str_ireplace('{plexis::messages}', self::ParseGlobalMessages(), $contents);
        $contents = str_ireplace('{plexis::elapsedtime}', Benchmark::ElapsedTime('total_script_exec', 5), $contents);
        
        // Set session > user var
        self::$variables['session']['user'] = Auth::GetUserData();
        
        // Set variables that were set in the Template object
        $Layout = new View($contents, false);
        foreach(self::$variables as $k => $v)
            $Layout->Set($k, $v);
        
        // Now, set template default variables
        $Layout->Set('BASE_URL', Request::BaseUrl());
        $Layout->Set('SITE_URL', SITE_URL);
        $Layout->Set('TEMPLATE_URL', self::$themeUrl);
        $Layout->Set('TEMPLATE_NAME', self::$themeConfig->info->name);
        $Layout->Set('TEMPLATE_AUTHOR', self::$themeConfig->info->author);
        $Layout->Set('TEMPLATE_CODED_BY', self::$themeConfig->info->coded_by);
        $Layout->Set('TEMPLATE_COPYRIGHT', self::$themeConfig->info->copyright);
        $Layout->Set('config', Config::FetchVars('Plexis'));
        
        // Return the rendered data
        return preg_replace('/<!--#.*#-->/iUs', '', $Layout->Render());
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
class ThemeNotSetException extends \Exception {}

/**
 * Thrown by the Template Class if the theme path provided is an invalid path
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Template.php
 * @see         Template::SetThemePath()
 */
class InvalidThemePathException extends \Exception {}

/**
 * Thrown by the Template Class if the contents provided are not a string, or subclass of the View method
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Template.php
 * @see         Template::Add()
 */
class InvalidPageContents extends \Exception {}

/**
 * Thrown by the Template Class if the theme is missing its config file
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Template.php
 * @see         Template::SetThemePath()
 */
class MissingThemeConfigException extends \Exception {}