<?php
/* 
| --------------------------------------------------------------
| 
| Plexis Template Engine
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: MY_Template
| ---------------------------------------------------------------
|
| Main template parsing / output file.
|
*/
namespace Application\Library;

class Template
{
    // An array of all template variables
    protected $variables = array();
    
    // Template information
    protected $template = array();
    
    // Our Compiler trigger
    protected $trigger = "Compiler";
    
    // Left and right variable delimiters
    protected $l_delim = '{';
    protected $r_delim = '}';
    
    // Our template type
    protected $type = "site";

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/   
    public function __construct() 
    {
        // Define defaults
        $this->_controller = $GLOBALS['controller'];
        $this->_action = $GLOBALS['action'];
        $this->_is_module = $GLOBALS['is_module'];
    }

/*
| ---------------------------------------------------------------
| Function: set()
| ---------------------------------------------------------------
|
| This method sets variables to be replace in the template system
|
| @Param: $name - Name of the variable to be set
| @Param: $value - The value of the variable
|
*/

    public function set($name, $value) 
    {
        $this->variables[$name] = $value;
    }
    
/*
| ---------------------------------------------------------------
| Function: set_template()
| ---------------------------------------------------------------
|
| Sets the template as a site template
|
| @Param: $name - The defined Template name
|
*/
    public function set_template($name = "default") 
    {
        // Set out type as site template
        $this->type = "site";
        
        // Set our actual template now
        $this->template['name'] = $name;
        $this->template['path'] = APP_PATH . DS . 'templates' . DS . $name;
        $this->template['http_path'] = SITE_URL . "/application/templates/". $name;
    }
    
/*
| ---------------------------------------------------------------
| Function: admin_template()
| ---------------------------------------------------------------
|
| Sets the template as the admin template
|
*/
    public function admin_template() 
    {
        // Set out type as site template
        $this->type = "admin";

        // Set paths
        $this->template['path'] = APP_PATH . DS . 'admin';
        $this->template['http_path'] = SITE_URL . "/application/admin";

    }

/*
| ---------------------------------------------------------------
| Function: set_delimiters()
| ---------------------------------------------------------------
|
| Sets the template delimiters for psuedo blocks
|
| @Param: $l - The left delimiter
| @Param: $r - The right delimiter
|
*/	
    public function set_delimiters($l = '{', $r = '}')
    {
        $this->l_delim = $l;
        $this->r_delim = $r;
    }
    
/*
| ---------------------------------------------------------------
| Function: load(path)
| ---------------------------------------------------------------
|
| Checks whether there is a template file and if its readable.
| Stores contents of file if read is successfull
|
| @Param: $file - Full file name. Can also be: "path/to/file.ext"
|
*/
    protected function load($file) 
    {
        $template_file = $this->template['path'] . DS . $file;
        
        // Fix a correction with some servers being real sensative to the DS
        // As well as having different DS's
        $template_file = str_replace('\\', DS, $template_file);
        
        // Make sure the file exists!
        if(!file_exists($template_file)) 
        {
            show_error('template_load_error', array($template_file), E_ERROR);
        }

        // Get the file contents and return
        return file_get_contents($template_file);
    }

/*
| ---------------------------------------------------------------
| Function: load_view()
| ---------------------------------------------------------------
|
| Gets the current page contents, checks if the template has
| a custom view for the page we are viewing
|
*/
    private function load_view()
    {
        // First we check to see if the template has a custom view for this page
        if($this->type !== 'admin')
        {
            $file = $this->template['path'] . DS . 'views' . DS . $this->_controller . DS . $this->view_file .'.php';
        }
        else
        {
            $file = $this->template['path'] . DS . 'views' . DS . $this->view_file .'.php';
        }
        
        // Check if the file exists
        if(file_exists($file))
        {
            return file_get_contents($file);
        }
        
        // No template custom view, load default
        else
        {
            if($this->_is_module == TRUE)
            {
                $file = APP_PATH . DS . 'modules' . DS . $this->_controller . DS . 'views' . DS . $this->view_file .'.php';
            }
            else
            {
                $file = APP_PATH . DS . 'views' . DS . $this->_controller . DS . $this->view_file .'.php';
            }
            if(!file_exists($file))
            {
                show_error('missing_page_view', array( $this->_controller .'/'.$this->view_file .'.php' ));
            }
            return file_get_contents($file);
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: view_js_string()
| ---------------------------------------------------------------
|
| Loads the views JS string if there is on.
|
*/
    private function view_js_string()
    {
        // Add the page JS if it exists as well
        if($this->type == 'site')
        {
            // Build our custom view JS path, and Static View Paths
            $t_file = $this->template['path'] . DS . 'views' . DS . $this->_controller . DS . 'js' . DS . $this->view_file . '.js';
            $s_file = APP_PATH . DS . 'static'. DS .'js'. DS .'views'. DS . $this->_controller . DS .$this->view_file.'.js';
            if(file_exists( $t_file ))
            {
                return '<script type="text/javascript" src="{TEMPLATE_URL}/views/'. $this->_controller .'/js/'.$this->view_file.'.js"></script>';
            }
            elseif(file_exists( $s_file ))
            {
                return '<script type="text/javascript" src="{SITE_URL}/application/static/js/views/'. $this->_controller .'/'.$this->view_file.'.js"></script>';
            }
            else
            {
                return ''; 
            }
        }
        return '';
    }

/*
| ---------------------------------------------------------------
| Function: compile()
| ---------------------------------------------------------------
|
| This method helps the compiler eval certain parts of code
|
| @Param: (String) $source - The code to be eval'd
| @Return: (String) - The eval'd string
|
*/
    private function compiler_eval($source) 
    {	
        // Look for Compiler eval Messages, We must process these first!
        if(strpos($source, $this->l_delim . $this->trigger .":eval") !== FALSE)
        {
            // Create our regex and fid all matches in the source
            $regex = $this->l_delim . $this->trigger .":eval". $this->r_delim ."(.*)". $this->l_delim . "/". $this->trigger .":eval". $this->r_delim;
            preg_match_all("~". $regex  ."~iUs", $source, $matches, PREG_SET_ORDER);
            
            // Loop through each match and eval it
            foreach($matches as $match)
            {
                ob_start();
                    eval('?>'.$match[1]);
                $content = ob_get_contents();
                ob_end_clean();
                $source = str_replace($match[0], $content, $source);
            }
        }
        
        return $source;
    }
	
/*
| ---------------------------------------------------------------
| Function: compile()
| ---------------------------------------------------------------
|
| This method compiles the template page by processing the template
| trigger events such as partial loading etc etc
|
| @Param: (Bool) $layout: Load the template's layout file?
| @Param: (String) $source: The contents to be compiled IF 
|   $layout == TRUE
| @Return (None)
|
*/
    private function compile($layout = TRUE, $source = NULL) 
    {
        // Get our template layout file
        ($layout == TRUE) ? $source = $this->load('layout.php') : '';
        
        // Load page contents so they can be parsed as well!
        $source = str_replace("{PAGE_CONTENTS}", $this->load_view(), $source); 
        
        // Add the page JS if it exists as well
        $js = $this->view_js_string();
        $source = str_replace("{VIEW_JS}", $js, $source);
        
        // Strip custom comment blocks
        while(preg_match('/<!--#.*#-->/iUs', $source, $replace)) 
        {
            $source = str_replace($replace[0], '', $source);
        }
        
        // Search and process compiler:eval commands
        $source = $this->compiler_eval($source);
        
        // Now, Loop through any remaining matchs of { TRIGGER : ... }
        if(strpos($source, $this->l_delim . $this->trigger .":") !== FALSE)
        {
            // Loop through each match of { TRIGGER : ... }
            $regex = $this->l_delim . $this->trigger .":(.*)". $this->r_delim;
            
            // Loop though each match and process accordingly
            while(preg_match("~". $regex  ."~iUs", $source, $replace))
            {
                // Assign the matches as $main
                $main = trim($replace[1]);
                
                // === Here we figure out what and how we are replacing === //
                
                    $exp = explode(":", $main);
                    
                    // Figure out what the task is EI: load
                    switch($exp[0])
                    {
                        case "load":
                            $content = $this->load($exp[1]);
                            $content = $this->compiler_eval($content);
                            break;
                            
                        case "constant":
                            ( defined($exp[1]) ) ? $content = constant($exp[1]) : $content = $this->l_delim . $exp[1] . $this->r_delim;
                            break;
                            
                        default:
                            show_error('unknown_template_command', array($exp[0]), E_ERROR);
                            break;
                    }
                
                // strip parsed Template block
                $source = str_replace($replace[0], $content, $source);
            }
        }
        
        $this->source = $source;
        return;
    }

/*
| ---------------------------------------------------------------
| Function: parse()
| ---------------------------------------------------------------
|
| This method oversees the Parser class in replacing all template
| variables with real ones
|
| @Return (None)
|
*/
    private function parse()
    { 
        // Init our emtpy message block
        $message_block = '';
        
        // If we have Global template messages
        if(isset($GLOBALS['template_messages']))
        {
            // Loop through each message, and add it to the message blcok
            foreach($GLOBALS['template_messages'] as $message)
            {
                $message_block .= $message;
            }
        }
        
        // Replace Global template messages
        $this->source = str_replace('{GLOBAL_MESSAGES}', $message_block, $this->source);
        
        // use the real parser
        $this->source = load_class('Parser', 'Library')->parse($this->source, $this->variables);
        
        // we are done
        return;
    }

/*
| ---------------------------------------------------------------
| Function: init()
| ---------------------------------------------------------------
|
| This method setups all the template variables for the render
| function
|
| @Return (None)
|
*/
    private function Init($data)
    {
        // define template url
        define('TEMPLATE_URL', $this->template['http_path']);
        define('TEMPLATE_PATH', $this->template['path']);
        
        // Add session data to our data array
        $data['session'] = load_class('Session', 'Library')->data;
        
        // Add user defined contstants to the template variables list
        $const = get_defined_constants(true);
        if(count($const['user'] > 0))
        {
            foreach($const['user'] as $key => $value)
            {
                $this->variables[$key] = $value;
            }
        }
        unset($const);
        
        // Add the passed variables to the template variables list
        if(count($data) > 0)
        {
            foreach($data as $key => $value)
            {
                $this->variables[$key] = $value;
            }
        }
        unset($data);
        
        // we are done
        return;
    }

/*
| ---------------------------------------------------------------
| Function: render()
| ---------------------------------------------------------------
|
| This method displays the page. It loads the header, footer, and
| view of the page.
|
| @Param: $view_name - The view file name
| @Param: $data - An array of variables that are to be passed to 
|       the View
|
*/

    public function render($view_name, $data = array()) 
    {
        // Define our view file name
        $this->view_file = $view_name;
        
        // Compile all the variables into one array
        $this->Init($data);

        // Compile the page
        $this->compile();

        // Run through the parser
        $this->parse();
        
        // use output buffering to catch the page. We do this so 
        // we can catch php errors in the template
        ob_start();
        
        // Extract the variables so $this->variables[ $var ] becomes just " $var "
        extract($this->variables);
        
        // Eval the page
        eval('?>'. $this->source);
        
        // Capture the contents and call it a day
        $this->source = ob_get_contents();
        ob_end_clean();
        
        // Get the absolute best benchmarks we can get
        $Benchmark = load_class('Benchmark');
        $this->source = str_replace('{MEMORY_USAGE}', $Benchmark->memory_usage(), $this->source);
        $this->source = str_replace('{ELAPSED_TIME}', $Benchmark->elapsed_time('system'), $this->source);
        
        // Do we use Gzip? Probably wont be in the release
        if(config('enable_gzip') == 1)
        {
            // No default content encoding
            $encoding = false;
            
            // If we havent sent headers yet, and our client isnt the w3c validator, attempt to zip the contents
            if( headers_sent() == FALSE && $_SERVER['HTTP_USER_AGENT'] != 'W3C_Validator' )
            {
                if( strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false )
                {
                    $encoding = 'x-gzip';
                    log_message('Encoding in X-Gzip');
                }
                elseif( strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false )
                {
                    $encoding = 'gzip';
                }
            }
            
            // If we have encoding, use it!
            if( $encoding )
            {
                // Send out headers for G-Zip
                header('Content-Encoding: '.$encoding);
                header('Cache-Control: must-revalidate');
                print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
                $size = strlen($this->source);
                $this->source = gzcompress($this->source, 5);
                $this->source = substr($this->source, 0, $size);
            }
        }
        
        // Echo the page to the browser
        echo $this->source;
    }
}
// EOF