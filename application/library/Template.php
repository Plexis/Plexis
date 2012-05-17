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
| Class: Template()
| ---------------------------------------------------------------
|
| The cms' template engine
|
*/
namespace Application\Library;

class Template
{
    // An array of all template variables
    protected $variables = array();
    protected $jsvars = array();
    
    // Array of custom css and JS files
    protected $jsfiles = array();
    protected $cssfiles = array();
    
    // Meta data from the <head> tags
    protected $_metadata = array();
    
    // Template information
    protected $template = array(
        'path' => NULL,                     // Our template path
        'http_path' => NULL,                // Our template HTTP path including site URL
    );
    
    // Our template xml file contents
    public $xml = NULL;
    
    // An array of template configs
    protected $config = array(
        'trigger' => "pcms::",              // Our Compiler trigger
        'l_delim' => '{',                   // Left variable delimeter    
        'r_delim' => '}',                   // Right variable delimeter
        'module_view_paths' => TRUE,        // If using a modules, use the module/views folder for view paths? FALSE is default paths
        'load_view_js' => TRUE              // Load the view js file        
    );
    
    // Our viewname
    protected $view_file;
    
    // Our loader and Session
    protected $load;
    protected $session;

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
        
        // Load the loader, Session, and Parser classes
        $this->load = load_class('Loader');
        $this->session = $this->load->library('Session');
        $this->parser = $this->load->library('Parser');
        
        // Set basic headings so they can be modified later!
        $this->append_metadata("<!-- Basic Headings -->")
             ->set_metadata('title', config('site_title'))
             ->set_metadata('keywords', config('meta_keywords'))
             ->set_metadata('description', config('meta_description'))
             ->set_metadata('generator', 'Plexis')
             ->append_metadata("") // Added whitespace
             ->append_metadata("<!-- Content type, And cache control -->")
             ->set_metadata('content-type', 'text/html; charset=UTF-8', 'http-equiv')
             ->set_metadata('cache-control', 'no-cache', 'http-equiv')
             ->set_metadata('expires', '-1', 'http-equiv');
    }
    
/*
| ---------------------------------------------------------------
| Function: config()
| ---------------------------------------------------------------
|
| Sets template config options
|
| @Param: $params - A Config key OR An array of template config 
|   options ( array($key => $value) )
| @Param: $value - The config value if $params is a config key string 
|
*/	
    public function config($params, $value = null)
    {
        if(is_array($params))
        {
            foreach($params as $key => $v)
            {
                if(isset($this->config[$key]))
                {
                    $this->config[$key] = $v;
                }
            }
        }
        else
        {
            if(isset($this->config[$params]))
            {
                $this->config[$params] = $value;
            }
        }
        return $this;
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
        return $this;
    }
    
/*
| ---------------------------------------------------------------
| Function: setjs()
| ---------------------------------------------------------------
|
| This method sets javascript variables to be replace in the 
|   template system header
|
| @Param: $name - Name of the variable to be set
| @Param: $value - The value of the variable
|
*/

    public function setjs($name, $value) 
    {
        $this->jsvars[$name] = $value;
        return $this;
    }
    
/*
| ---------------------------------------------------------------
| Function: set_template_path()
| ---------------------------------------------------------------
|
| Sets the template path from the Cms Root folder
|
*/
    public function set_template_path($path = 'application/template') 
    {
        // Set template path
        $path = trim($path, '/\\');
        $this->template['http_path'] = BASE_URL ."/" . str_replace( '\\', '/', $path );
        $this->template['path'] = str_replace( array('/', '\\'), DS, $path );
        return $this;
    }
    
/*
| ---------------------------------------------------------------
| Function: set_controller()
| ---------------------------------------------------------------
|
| This method is used to manually overried the controller path
|
*/

    public function set_controller($controller, $module = false) 
    {
        $this->_controller = $controller;
        $this->_is_module = $module;

    }

    
/*
| ---------------------------------------------------------------
| Template Header Building Functions
| ---------------------------------------------------------------
*/

  
/*
| ---------------------------------------------------------------
| Function: add_script()
| ---------------------------------------------------------------
|
| This method adds a JS scr to the header in the template
|
| @Param: $path - This can be 1 of 3 things...
|   1) The path to the JS files. Must be from the 
|       cms root folder... (Ex: "application/static/js/file.js")
|       of a full http path
|   2) Js filename. In which the Application/static/js folder is
|       used as the path, or if the JS file exists in the template
|       JS folder, that is loaded instead.
|   3) A full http:// path to the js file
|
*/

    public function add_script($path)
    {
        $name = basename($path);
        $this->jsfiles[$name] = $path;
        return $this;
    }
    
/*
| ---------------------------------------------------------------
| Function: add_css()
| ---------------------------------------------------------------
|
| This method adds a CSS scr to the header in the template
|
| @Param: $path - This can be 1 of 2 things...
|   1) Css filename. In which the <template_path>/<css_folder> is
|       used as the path.
|   3) A full http:// path to the Css file
|
*/

    public function add_css($path)
    {
        $name = basename($path);
        $this->cssfiles[$name] = $path;
        return $this;
    }
    
/*
| ---------------------------------------------------------------
| Function: prepend_metadata()
| ---------------------------------------------------------------
|
| This method adds a new line of JS | css | meta tags into the head data
|
| @Param: (String) $line - The new line to be prepended (added before)
| @Return (Object) $this
|
*/
    public function prepend_metadata($line)
    {
        array_unshift($this->_metadata, $line);
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: append_metadata()
| ---------------------------------------------------------------
|
| This method adds a new line of JS | css | meta tags into the head data
|
| @Param: (String) $line - The new line to be appended (added after)
| @Return (Object) $this
|
*/
    public function append_metadata($line)
    {
        $this->_metadata[] = $line;
        return $this;
    }
    
/*
| ---------------------------------------------------------------
| Function: set_metadata()
| ---------------------------------------------------------------
|
| This method sets a specfic line of meta tags into the head data
|
| @Param: (String) $name - The name of the meta tag Ex: language
| @Param: (String) $content - The content (value) of this tag
| @Param: (String) $type - Type of tag we are setting
| @Return (Object) $this
|
*/
    public function set_metadata($name, $content, $type = 'meta')
    {
        // Convert our name and contents to html friendly
        $name = htmlspecialchars(strip_tags($name));
        $content = htmlspecialchars(strip_tags($content));
        
        // if name is title, then we can process the type as title!
        if($name == 'title') $type = 'title';

        switch($type)
        {
            case 'meta':
                $this->_metadata[$name] = '<meta name="'.$name.'" content="'.$content.'"/>';
                break;
            
            case 'http-equiv':
                $this->_metadata[$name] = '<meta http-equiv="'.$name.'" content="'.$content.'"/>';
                break;

            case 'link':
                $this->_metadata[$content] = '<link rel="'.$name.'" href="'.$content.'"/>';
                break;
            
            case 'title':
                $this->_metadata['title'] = '<title>'.$content.'</title>';
                break;
            
            case 'base':
                $this->_metadata['base'] = '<base href="'.$content.'" target="_self"/>';
                break;
        }

        return $this;
    }
    
/*
| ---------------------------------------------------------------
| Template Rendering Functions
| ---------------------------------------------------------------
*/


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
| @Param: $layout - The layout filename. Set to FALSE for no layout
|
*/

    public function render($view_name, $data = array()) 
    {
        // Define our view file name, and layout filename
        $this->view_file = str_replace( array('/', '\\'), DS, $view_name);
        
        // Compile all the variables into one array
        $this->_initialize($data);

        // Compile the page
        $this->compile();

        // Run through the parser
        $this->parse();
        
        // use output buffering to catch the page. We do this so 
        // we can catch php errors in the template as well
        ob_start();
        
        // Extract the variables so $this->variables[ $var ] becomes just " $var "
        extract($this->variables);
        
        // Eval the page so we can process the php in the template correctly
        eval('?>'. $this->source);
        
        // Capture the contents so we can do some search and replacing
        $this->source = ob_get_contents();
        ob_end_clean();
        
        // Set our page load times
        $Benchmark = load_class('Benchmark');
        $this->source = str_replace('{MEMORY_USAGE}', $Benchmark->memory_usage(), $this->source);
        $this->source = str_replace('{ELAPSED_TIME}', $Benchmark->elapsed_time('system'), $this->source);
        
        // Start a new output buffer
        ob_start();
        
        // Echo the page to the browser
        echo $this->source;
        
        // flush the buffers
        ob_end_flush();
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
    protected function compile() 
    {
        // Shorten up the text here
        $l_delim = "<";
        $r_delim = ">";
        $trigger = $this->config['trigger'];
        
        // Get our template layout file
        $c = $this->_controller;
        $a = $this->_action;
        $name = eval('if( $this->xml->layouts && $this->xml->layouts->'.$c.'->'.$a.' ) return $this->xml->layouts->'.$c.'->'.$a.'; return FALSE; ');
        if( !$name )
        {
            $name = $this->xml->config->default_layout;
        }
        
        // load the layout
        $source = trim( $this->load( $name .'.php' ) );
        
        // Load global page contents early so they can be parsed as well!
        if(strpos( $source, $l_delim . $trigger ."page_contents /". $r_delim ))
        {
            $source = str_replace($l_delim . $trigger ."page_contents /". $r_delim, $this->load_view(), $source); 
        }

        // Search and process <trigger::eval> commands
        $source = $this->compiler_eval($source);
        
        // Now, Loop through any partial page includes
        $search = $l_delim . $trigger ."include";
        if(strpos($source, $search) !== FALSE)
        {
            // Loop through each match of < TRIGGER ... />
            $regex = $l_delim . $trigger ."include name=(.*) /". $r_delim;
            
            // Loop though each match and process accordingly
            while(preg_match("~". $regex  ."~iUs", $source, $replace))
            {
                // Assign the matches as $main
                $main = trim($replace[1], "\"'");
                $main = str_replace( array('\\', '/'), DS, $main );
                $file = $this->template['path'] . DS . $main;
                
                // Load the file, and eval it
                $content = $this->load($file);
                $content = $this->compiler_eval($content);
                
                // strip parsed Template block
                $source = str_replace($replace[0], $content, $source);
            }
        }
        
        // Build our header string
        $search = $l_delim . $trigger ."head /" . $r_delim;
        if(strpos($source, $search) !== FALSE)
        {
            $source = str_replace($search, $this->_build_header(), $source);
        }
        
        // Find and replace our global message block!
        $search = $l_delim . $trigger ."global_messages /" . $r_delim;

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
        
        // Get our global messages in here
        $source = str_replace($search, $message_block, $source);
        
        // Lastely, Strip template comment blocks
        while(preg_match('/<!--#.*#-->/iUs', $source, $replace)) 
        {
            $source = str_replace($replace[0], '', $source);
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
    protected function parse()
    {
        // Shorten up the text here
        $l_delim = $this->config['l_delim'];
        $r_delim = $this->config['r_delim'];

        // Use the Parser
        $this->parser->set_delimiters($l_delim, $r_delim);
        $this->source = $this->parser->parse($this->source, $this->variables);
        
        // we are done
        return;
    }

    
/*
| ---------------------------------------------------------------
| Template Loading / Helper Methods
| ---------------------------------------------------------------
*/

    
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
        $template_file = str_replace( array('/', '\\'), DS, $template_file);
        
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
    protected function load_view()
    {
        // First we check to see if the template has a custom view for this page
        $ext = '';
        
        // Determine our path
        if($this->_is_module && $this->config['module_view_paths'])
        {
            $file = APP_PATH . DS . 'modules' . DS . $this->_controller . DS . 'views' . DS . $this->view_file .'.php';
        }
        else
        {
            if( $this->xml->config->controller_view_paths == "true" && !$this->_is_module)
            {
                $ext = $this->_controller . DS;
            }
            // Build our template view path
            $file = $this->template['path'] . DS . 'views' . DS . $ext . $this->view_file .'.php';
        }
        
        // Check if the file exists
        if(file_exists($file))
        {
            return file_get_contents($file);
        }
        
        // No template custom view, load default
        elseif( !$this->_is_module )
        {
            $file = APP_PATH . DS . 'assets' . DS . 'default_views' . DS . $this->_controller . DS . $this->view_file .'.php';
            if(file_exists($file))
            {
                return file_get_contents($file);
            }
        }

        // If we are here, we have no view to load
        show_error('missing_page_view', array( $this->view_file .'.php' ));
    }
    
/*
| ---------------------------------------------------------------
| Function: load_template_xml()
| ---------------------------------------------------------------
|
| This method loads the template xml, and sets the template info
|   into an array
|
| @Return (None)
|
*/
    public function load_template_xml($path = NULL)
    {
        // Shorten up the text here
        if($path == NULL)
        {
            $file = $this->template['path'] . DS .'template.xml';
        }
        else
        {
            $path = str_replace( array('/', '\\'), DS, $path );
            $file = APP_PATH . DS . $path . DS .'template.xml';
        }
        
        // Load the template xml fil if it exists
        if(file_exists( $file ))
        {
            if($path == NULL)
            {
                $this->xml = simplexml_load_file($file);
            }
            else
            {
                return simplexml_load_file($file);
            }
        }
        else
        {
            if($path == NULL)
            {
                show_error('Unable to load the template.xml');
            }
            else
            {
                return FALSE;
            }
        }
        return;
    }
    
/*
| ---------------------------------------------------------------
| Function: view_js_string()
| ---------------------------------------------------------------
|
| Loads the views JS string if there is on.
|
*/
    protected function view_js_string()
    {
        // Build our custom view JS path, and Static View Paths
        if( $this->config['load_view_js'] )
        {
            $t_file = $this->template['path'] . DS . 'js'. DS . $this->_controller . DS . $this->view_file .'.js';
            $s_file = APP_PATH . DS . 'static'. DS . 'js'. DS . $this->_controller . DS . $this->view_file .'.js';
            if(file_exists( $t_file ))
            {
                return '<script type="text/javascript" src="{TEMPLATE_URL}/js/'. $this->_controller .'/'.$this->view_file.'.js"></script>';
            }
            elseif(file_exists( $s_file ))
            {
                return '<script type="text/javascript" src="'. BASE_URL .'/application/static/js/'. $this->_controller .'/'.$this->view_file.'.js"></script>';
            }
        }
        return false; 
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
    protected function compiler_eval($source) 
    {	
        // Shorten up the text here
        $l_delim = "<";
        $r_delim = ">";
        $trigger = $this->config['trigger'];
        
        // Look for Compiler eval Messages, We must process these first!
        if(strpos($source, $l_delim . $trigger ."eval") !== FALSE)
        {
            // Create our regex and fid all matches in the source
            $regex = $l_delim . $trigger ."eval". $r_delim ."(.*)". $l_delim . "/". $trigger ."eval". $r_delim;
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
| Template Header Compilation / Initialization Methods
| ---------------------------------------------------------------
*/


/*
| ---------------------------------------------------------------
| Function: _initialize()
| ---------------------------------------------------------------
|
| This method setups up just about the whole template system
|
| @Return (None)
|
*/
    protected function _initialize($data)
    {
        // Set our template paths and all that if the we dont have one
        if($this->template['path'] == NULL) $this->set_template_path();

        // set the absolute template path now
        $this->template['path'] = ROOT . DS . $this->template['path'];
        
        // Load the template information
        if($this->xml == NULL) $this->load_template_xml();
        
        // Load template helpers if required
        if( $this->xml->helpers )
        {
            foreach($this->xml->helpers->children() as $helper)
            {
                $this->load->helper($helper);
            }
        }

        // Add session data to our data array
        $data['session'] = $this->session->data;
        
        // Add the config...
        $configs = load_class('Config')->get_all('App');
        if(isset($data['config']))
        {
            $data['config'] = array_merge($configs, $data['config']);
        }
        else
        {
            $data['config'] = $configs;
        }
        
        // Add the passed variables to the template variables list
        foreach($data as $key => $value)
        {
            $this->set($key, $value);
        }
        unset($data);
        
        // Set all the template data LAST! Also squeeze the site url in there
        $this->set('BASE_URL', BASE_URL);
        $this->set('SITE_URL', SITE_URL);
        $this->set('TEMPLATE_URL', $this->template['http_path']);
        $this->set('TEMPLATE_PATH', $this->template['path']);
        $this->set('TEMPLATE_NAME', $this->xml->info->name);
        $this->set('TEMPLATE_AUTHOR', $this->xml->info->author);
        $this->set('TEMPLATE_CODED_BY', $this->xml->info->coded_by);
        $this->set('TEMPLATE_COPYRIGHT', $this->xml->info->copyright);
        
        // we are done
        return;
    }
    
/*
| ---------------------------------------------------------------
| Function: _build_header()
| ---------------------------------------------------------------
|
| This method fills the <head> tag with set meta data
|
| @Return (String) The formatted head string
|
*/
    protected function _build_header()
    {
        // Convert our JS vars into a string :)
        $string = 
        "        var Plexis = {
            url : '". SITE_URL ."',
            realm_id : ". get_realm_cookie() .",
            template_url : '{TEMPLATE_URL}',
        }\n";
        foreach($this->jsvars as $key => $val)
        {
            // Format the var based on type
            $val = (is_numeric($val)) ? $val : '"'. $val .'"';
            $string .= "        var ". $key ." = ". $val .";\n";
        }
        
        // Remove last whitespace
        $string = rtrim($string);
        
        // Setup the basic static headings
        $this->append_metadata("") // Add whitespace
             ->append_metadata("<!-- Include jQuery Scripts -->")
             ->append_metadata('<script type="text/javascript" src="'. BASE_URL .'/application/static/js/jquery.js"></script>')
             ->append_metadata('<script type="text/javascript" src="'. BASE_URL .'/application/static/js/jquery-ui.js"></script>')
             ->append_metadata('<script type="text/javascript" src="'. BASE_URL .'/application/static/js/jquery.validate.js"></script>')
             ->append_metadata("") // Add whitespace
             ->append_metadata("<!-- Define Global Vars and Include Plexis Static JS Scripts -->")
             ->append_metadata(
    '<script type="text/javascript">
'. $string .'
    </script>')
             ->append_metadata('<script type="text/javascript" src="'. BASE_URL .'/application/static/js/plexis.js"></script>'
        );
        
        // Append the template meta data
        $this->_append_template_metadata();
        
        // Append loaded css files
        if( !empty($this->cssfiles) )
        {
            $this->append_metadata(""); // Add whitespace
            $this->append_metadata("<!-- Include Controller Defined Css Files -->");
            foreach($this->cssfiles as $f)
            {
                // Find the path :O
                if(preg_match('@^(ftp|http(s)?)://@i', $f))
                {
                    // We have a full url
                    $src = $f;
                }
                elseif(strpos($f, 'application/') !== false)
                {
                    // Application Path
                    $f = ltrim($f, '/');
                    $src = BASE_URL . '/'. $f;
                }
                else
                {
                    // Just a filename
                    $src = $this->template['http_path'] . '/' . trim($this->xml->config->css_folder, '/') . '/' . $f;
                }
                
                // Add the stylesheet to the header
                $this->set_metadata('stylesheet', $f, 'link');
            }
        }
        
        // Append loaded js files
        if( !empty($this->jsfiles) )
        {
            $this->append_metadata(""); // Add whitespace
            $this->append_metadata("<!-- Include Controller Defined JS Files -->");
            foreach($this->jsfiles as $j)
            {
                // Find the path :O
                if(preg_match('@^(ftp|http(s)?)://@i', $j))
                {
                    // We have a full url
                    $src = $j;
                }
                elseif(strpos($j, 'application/') !== false)
                {
                    // Application Path
                    $j = ltrim($j, '/');
                    $src = BASE_URL . '/'. $j;
                }
                else
                {
                    // Just a filename
                    $path = $this->template['path'] . DS . trim($this->xml->config->js_folder, '/') . DS . $j;
                    if(file_exists( str_replace(array('/', '\\'), DS, $path) ))
                    {
                        $src = $this->template['http_path'] . '/' . trim($this->xml->config->js_folder, '/') . '/' . $j;
                    }
                    else
                    {
                        $src = BASE_URL . '/application/static/js/'. $j;
                    }
                }
                
                // Add the js path
                $this->append_metadata('<script type="text/javascript" src="'. $src .'"></script>');
            }
        }
        
        // Add the view if we have one
        $line = $this->view_js_string();
        if($line != false)
        {
            $this->append_metadata( '' );
            $this->append_metadata( "<!-- Include the page view JS file -->" );
            $this->append_metadata( $line );
        }

        // Now, we build the header into a string
        $head = "";
        foreach($this->_metadata as $data)
        {
            $head .= "\t". trim($data) . "\n";
        }
        return $head;
    }
    
/*
| ---------------------------------------------------------------
| Function: _append_template_metadata()
| ---------------------------------------------------------------
|
| This method sets all the meta data from the template.xml
|
*/
    protected function _append_template_metadata()
	{
		// Add the template.xml head data to the metadata
        if( $this->xml->head )
        {
            foreach($this->xml->head->children() as $head)
            {
                $path = $this->template['http_path'] . '/';
                switch( $head->getName() )
                {
                    case "comment":
                        $this->append_metadata(''); // Add a space ontop
                        $this->append_metadata('<!-- '. wordwrap($head, 125) .' -->');
                        break;
                    
                    case "css":
                        $this->set_metadata('stylesheet', $path . trim($this->xml->config->css_folder, '/') . '/'. $head, 'link');
                        break;
                    
                    case "js":
                        $this->append_metadata('<script type="text/javascript" src="'. $path . trim($this->xml->config->js_folder, '/') . '/'. $head .'"></script>');
                        break;
                    
                    case "favicon":
                        // Check to see if we are generous enough to have a filetype
                        if(isset($head['type']))
                        {
                            $ext = $head['type'];
                        }
                        else
                        {
                            // We need to manually get the image mime type :(
                            $ext = pathinfo($this->template['path'] . DS . $head, PATHINFO_EXTENSION);
                            $ext = "image/".$ext;
                        }
                        $this->append_metadata('<link rel="icon" type="'. $ext .'" href="'. $path . $head .'" />');
                        break;
                    
                    case "meta":
                        $quit = FALSE;
                        $i = 0;
                        foreach( $head->attributes() as $k => $v )
                        {
                            // Check to see if we are overwriting one thats set already
                            if($k == 'name' || $k == 'http-equiv')
                            {
                                if($k == 'name') $this->set_metadata($v, $head);
                                if($k == 'http-equiv') $this->set_metadata($v, $head, 'http-equiv');
                                $quit = TRUE;
                                break;
                            }
                            $attr .= $k .'="'.$v.'" ';
                            ++$i;
                        }
                        
                        // IF quit is still false, we didnt overwright anything
                        if($quit == FALSE && $i > 0) $this->append_metadata('<meta '.$attr.' content="'.$head.'" />');
                        break;
                }
            }
        }
	}
}
// EOF