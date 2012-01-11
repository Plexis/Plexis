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
| Class: Template
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
    
    // Meta data from the <head> tags
    protected $_metadata = array();
    
    // Template information
    protected $template = array(
        'path' => NULL,                     // Our template path
        'http_path' => NULL,                // Our template HTTP path including site URL
        'info' => array()                   // Array of info from the template.xml file
    );
    
    // An array of template configs
    protected $config = array(
        'trigger' => "pcms::",              // Our Compiler trigger
        'controller_view_paths' => TRUE,    // Add the controller name to the view paths? (views/<controller>/view.php)
        'l_delim' => '{',                   // Left variable delimeter    
        'r_delim' => '}'                    // Right variable delimeter   
    );
    
    // Our viewname and layout name
    protected $view_file;
    protected $layout_file;
    
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
        
        // Build our custom keywords and description
        $title = config('site_title');
        $keywords = config('meta_keywords');
        $desc = config('meta_description');
        
        // Setup the basic static headings
        $this->append_metadata("<!-- Basic Headings -->")
             ->set_metadata('title', $title, 'title')
             ->set_metadata('keywords', $keywords)
             ->set_metadata('description', $desc)
             ->append_metadata("") // Added whitespace
             ->append_metadata("<!-- Content type, And cache control -->")
             ->set_metadata('content-type', 'text/html; charset=UTF-8', 'http-equiv')
             ->set_metadata('cache-control', 'no-cache', 'http-equiv')
             ->set_metadata('expires', '-1', 'http-equiv')
             ->append_metadata("") // Add whitespace
             ->append_metadata("<!-- Include Plexis Static JS Scripts -->")
             ->append_metadata('<script type="text/javascript" src="'. SITE_URL .'/application/static/js/jquery-1.6.2.min.js"></script>')
             ->append_metadata('<script type="text/javascript" src="'. SITE_URL .'/application/static/js/jquery.validate.min.js"></script>')
             ->append_metadata('<script type="text/javascript" src="'. SITE_URL .'/application/static/js/jquery.dataTables.min.js"></script>')
             ->append_metadata('<script type="text/javascript">var url = "'. SITE_URL .'"; var realm_id = '. get_realm_cookie() .';</script>'
        );
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
| Function: set_template_path()
| ---------------------------------------------------------------
|
| Sets the template path from the Application folder
|
*/
    public function set_template_path($path = 'templates') 
    {
        // Set template path
        $this->template['http_path'] = SITE_URL ."/application/". str_replace( '\\', '/', $path );
        $this->template['path'] = str_replace( array('/', '\\'), DS, $path );
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: set_layout()
| ---------------------------------------------------------------
|
| Sets the specific layout file we are going to use
|
*/    
    public function set_layout($layout)
    {
        $this->layout_file = str_replace( array('/', '\\'), DS, $layout);
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: set_delimiters()
| ---------------------------------------------------------------
|
| Sets the template config options
|
| @Param: $params - An array of template config options
|
*/	
    public function config($params)
    {
        foreach($params as $key => $value)
        {
            if(isset($this->config[$key]))
            {
                $this->config[$key] = $value;
            }
        }
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
        $name = htmlspecialchars(strip_tags($name));
        $content = htmlspecialchars(strip_tags($content));

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
        
        // Set our empty return data
        $helpers = array();
        $head = array();
        $infos = array();
        
        // Load the template xml fil if it exists
        if(file_exists( $file ))
        {
            $template = array();
            $Info = simplexml_load_file($file);
            
            // Process the info child
            foreach( $Info->info->children() as $child )
            {
                $infos[ $child->getName() ] = $child;
            }
            
            // Process helpers
            foreach( $Info->config->helpers->children() as $helper )
            {
                $helpers = $helper;
            }
            
            // Process head tags
            foreach( $Info->config->head->children() as $child )
            {
                $attr = FALSE;
                foreach( $child->attributes() as $key => $value )
                {
                    if($attr === FALSE) $attr = array();
                    $attr[$key] = $value;
                }
                $head[] = array('type' => $child->getName(), 'value' => $child, 'attr' => $attr);
            }
        }
        return array('info' => $infos, 'helpers' => $helpers, 'head' => $head);
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
| @Param: $layout - The layout filename. Set to FALSE for no layout
|
*/

    public function render($view_name, $data = array(), $layout = NULL) 
    {
        // Define our view file name, and layout filename
        $this->view_file = str_replace( array('/', '\\'), DS, $view_name);
        if($layout !== NULL) $this->set_layout($layout);
        
        // Compile all the variables into one array
        $this->_initialize($data);

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
        
        // Set our page load times
        $Benchmark = load_class('Benchmark');
        $this->source = str_replace('{MEMORY_USAGE}', $Benchmark->memory_usage(), $this->source);
        $this->source = str_replace('{ELAPSED_TIME}', $Benchmark->elapsed_time('system'), $this->source);

        // Do we use Gzip output?
        if(config('enable_gzip_output') == 1)
        {
            // No default content encoding
            $encoding = false;
            
            // If we havent sent headers yet, and our client isnt the w3c validator, attempt to zip the contents
            if( headers_sent() == FALSE && $_SERVER['HTTP_USER_AGENT'] != 'W3C_Validator' )
            {
                if( strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false )
                {
                    $encoding = 'x-gzip';
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
        if($this->layout_file !== FALSE && !empty($this->layout_file))
        {
            $source = $this->load( $this->layout_file .'.php' );
            
            // Load global page contents early so they can be parsed as well!
            $source = str_replace($l_delim . $trigger ."page_contents /". $r_delim, $this->load_view(), $source); 
        }
        else
        {
            $source = $this->load_view();
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
        $ext = NULL;
        if($this->config['controller_view_paths'] == TRUE)
        {
            $ext = $this->_controller . DS;
        }
        
        // Build our template view path
        $file = $this->template['path'] . DS . 'views' . DS . $ext . $this->view_file .'.php';
        
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
                $file = APP_PATH . DS . 'views' . DS . $ext . $this->view_file .'.php';
            }
            if(!file_exists($file))
            {
                show_error('missing_page_view', array( $this->view_file .'.php' ));
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
    protected function view_js_string()
    {
        // Build our custom view JS path, and Static View Paths
        $t_file = $this->template['path'] . DS . 'js'. DS .'views'. DS . $this->_controller . DS . $this->view_file .'.js';
        $s_file = APP_PATH . DS . 'static'. DS . 'js'. DS .'views'. DS . $this->_controller . DS . $this->view_file .'.js';
        if(file_exists( $t_file ))
        {
            return '<script type="text/javascript" src="{TEMPLATE_URL}/js/views/'. $this->_controller .'/'.$this->view_file.'.js"></script>';
        }
        elseif(file_exists( $s_file ))
        {
            return '<script type="text/javascript" src="'. SITE_URL .'/application/static/js/views/'. $this->_controller .'/'.$this->view_file.'.js"></script>';
        }
        else
        {
            return ''; 
        }
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
| Function: _append_template_metadata()
| ---------------------------------------------------------------
|
| This method sets all the meta data from the template.xml
|
*/
    protected function _append_template_metadata()
	{
		// Add the template.xml head data to the metadata
        foreach($this->template['head'] as $head)
        {
            $path = $this->template['http_path'] . '/';
            switch($head['type'])
            {
                case "comment":
                    $this->append_metadata(''); // Add a space ontop
                    $this->append_metadata('<!-- '. wordwrap($head['value'], 125) .' -->');
                break;
                
                case "css":
                    $this->set_metadata('stylesheet', $path . $head['value'], 'link');
                break;
                
                case "js":
                    $this->append_metadata('<script type="text/javascript" src="'. $path . $head['value'] .'"></script>');
                break;
                
                case "favicon":
                    // Check to see if we are generous enough to have a filetype
                    if(isset($head['attr']['type']))
                    {
                        $ext = $head['attr']['type'];
                    }
                    else
                    {
                        // We need to manually get the image mime type :(
                        $ext = pathinfo($this->template['path'] . DS . $head['value'], PATHINFO_EXTENSION);
                        $ext = "image/".$ext;
                    }
                    $this->append_metadata('<link rel="icon" type="'. $ext .'" href="'. $path . $head['value'] .'" />');
                break;
                
                case "meta":
                    $attr = '';
                    if($head['attr'] != FALSE)
                    {
                        $quit = FALSE;
                        foreach($head['attr'] as $k => $v)
                        {
                            // Check to see if we are overwriting one thats set already
                            if($k == 'name' || $k == 'http-equiv')
                            {
                                if($k == 'name') $this->set_metadata($v, $head['value']);
                                if($k == 'http-equiv') $this->set_metadata($v, $head['value'], 'http-equiv');
                                $quit = TRUE;
                                break;
                            }
                            $attr .= $k .'="'.$v.'" ';
                        }
                        
                        // IF quit is still false, we didnt overwright anything
                        if($quit == FALSE) $this->append_metadata('<meta '.$attr.' content="'.$head['value'].'" />');
                    }
                break;
            }
        }
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
        // Append the template meta data
        $this->_append_template_metadata();
        
        // Add the view if we have one
        $line = $this->view_js_string();
        if($line != '')
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
        $this->template['path'] = APP_PATH . DS . $this->template['path'];
        
        // Load the template information
        $this->template = array_merge($this->template, $this->load_template_xml());
        
        // Load template helpers if required
        if(count($this->template['helpers']) > 0)
        {
            foreach($this->template['helpers'] as $h)
            {
                $this->load->helper($h);
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
        $this->set('SITE_URL', SITE_URL);
        $this->set('TEMPLATE_URL', $this->template['http_path']);
        $this->set('TEMPLATE_PATH', $this->template['path']);
        $this->set('TEMPLATE_NAME', $this->template['info']['name']);
        $this->set('TEMPLATE_AUTHOR', $this->template['info']['author']);
        $this->set('TEMPLATE_CODED_BY', $this->template['info']['coded_by']);
        $this->set('TEMPLATE_COPYRIGHT', $this->template['info']['copyright']);
        
        // we are done
        return;
    }
}
// EOF