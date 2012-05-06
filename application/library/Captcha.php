<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Copyright:    Copyright (c) 2011-2012, Steven Wilson
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Captcha()
| ---------------------------------------------------------------
|
| Easy to use captcha image generator
|
*/
namespace Application\Library;

class Captcha
{
    // Our catpcha string
    protected $_string;

    // Path to our fonts
    protected $fontpath;

    // An array of all our fonts
    protected $fonts;

/*
| ---------------------------------------------------------------
| Contructer
| ---------------------------------------------------------------
*/
    public function __construct()
    {
        // Define where out fonts are stored, and load them
        $this->fontpath = APP_PATH . DS . 'library/fonts/';      
        $this->load_fonts();

        // Make sure we can even run this show!
        if(!function_exists('imagettftext'))
        {
            throw new \Exception('imagettftext() not found. GD library must not be installed. Captcha failed to start.');
        }
    }

/*
| ---------------------------------------------------------------
| Function: load_fonts()
| ---------------------------------------------------------------
|
| This method loads all the .ttf files from the font Dir.
|
*/	
    protected function load_fonts()
    {
        // Initiate our array of fonts, and open the font directory
        $fonts = array();    
        if ($handle = @opendir($this->fontpath))
        {
            // Start the loop, add each file to the list of ttf files.
            while(($file = readdir($handle)) !== FALSE)
            {      
                // Get the ext of each file
                $ext = pathinfo($file, PATHINFO_EXTENSION);
       
                // Only allow .ttf files
                if ($ext == 'ttf')
                {         	
                    $this->fonts[] = $file;
                }
            }
            closedir($handle);
        }
        
        // Couldnt open the Dir. 
        else
        {     	
            show_error('captcha_bad_font_dir', FALSE, E_ERROR);
        
        }
      
        // Make sure we have 1 or more fonts
        if(count($this->fonts) == 0)
        {
            log_message('No fonts found in the appliacation/library/fonts/ folder.');
            show_error('captcha_no_fonts', FALSE, E_ERROR);     	
        } 
    }

/*
| ---------------------------------------------------------------
| Function: get_random_font()
| ---------------------------------------------------------------
|
| This method returns a random font to be used in the captcha
|
*/	
    protected function get_random_font()
    {   
        return $this->fontpath . $this->fonts[ mt_rand(0, count($this->fonts) - 1) ];   
    }

/*
| ---------------------------------------------------------------
| Function: genertate_string()
| ---------------------------------------------------------------
|
| This method create a random string using the params below
|
| @Param: $length - The number of characters in the captcha
| @Param: $lc - use Lowercase?? TRUE or FALSE
| @Param: $uc - use Uppercase?? TRUE or FALSE
| @Param: $nbrs - use Numbers?? TRUE or FALSE
|
*/
    protected function generate_string($length, $lc, $uc, $nbrs)
    {
        $list = array();
        
        // Add uppercase, lowercase, and numbers based on users preference
        ($lc == TRUE) ? $list = array_merge($list, range('a', 'z')) : '';
        ($uc == TRUE) ? $list = array_merge($list, range('A', 'Z')) : '';
        ($nbrs == TRUE) ? $list = array_merge($list, range(2, 9)) : '';

        // Size of the array
        $size = count($list) - 1;

        // Add letters and numbers randomly to the string
        for ($i = 0; $i < $length; $i++)
        {
            $this->_string .= $list[mt_rand(0, $size)];
        }
        
        // Return the string :p
        return $this->_string;
    }

/*
| ---------------------------------------------------------------
| Function: display()
| ---------------------------------------------------------------
|
| Builds the captcha image
|
| @Param: $length - The number of characters in the captcha
| @Param: $fontsize - The size of the font used
| @Param: $imageheight - The hiehgt of the image in pixels
| @Param: $imagelength - The length of the image in pixels
| @Param: $lowercase - use Lowercase?? TRUE or FALSE
| @Param: $uppercase - use Uppercase?? TRUE or FALSE
| @Param: $numbers - use Numbers?? TRUE or FALSE
|
*/
    public function display($length = 6, $fontsize = 25, $imageheight = 75, $imagelength = NULL, $lowercase = FALSE, $uppercase = TRUE, $numbers = TRUE)
    {
        // Generate a random string
        $string = $this->generate_string($length, $lowercase, $uppercase, $numbers);
        $string_length = strlen($string);
        
        // Create image sizes, Length extends with more letters
        ($imagelength == NULL) ? $imagelength = ($length * 25) + 25 : '';

        // Create our empty image
        $image = imagecreate($imagelength, $imageheight);

        // Set BG color to default white
        $bgcolor = imagecolorallocate($image, 255, 255, 255);

        // Set our font color as black
        $fontcolor = imagecolorallocate($image, 0, 0, 0);

        // Create the background letters
        $this->add_bg_letters($image, $this->get_random_font());

        // Loop through and add our letters
        for($i = 0; $i < $string_length; $i++)
        {
            // Create image text
            imagettftext(
                $image, // Image Source
                $fontsize, // Font Size
                mt_rand(-20, 20), // Angle
                ($i * 25) + 10, // X
                mt_rand($fontsize, $imageheight - $fontsize), // Y
                $fontcolor, // Color
                $this->get_random_font(), // Font File
                $string[$i] // Text
            );
      
        }

        // Create the image png and destroy our temp image
        imagepng($image);      
        imagedestroy($image);
    }

/*
| ---------------------------------------------------------------
| Function: get_string()
| ---------------------------------------------------------------
|
| Returns the Catcha String
|
*/	
    public function get_string()
    {
        return $this->_string;
    }
	
/*
| ---------------------------------------------------------------
| Function: add_bg_letters()
| ---------------------------------------------------------------
|
| This method adds the background characters in the image
|
*/    
    protected function add_bg_letters($image, $font, $passes = 3)
    {
        // Get our image demensions
        $w = imagesx($image);
        $h = imagesy($image);
        
        // Set our font color to grey
        $fontcolor = imagecolorallocate($image, 150, 150, 150);
        
        // Get out list of letters we will use
        $letters = range('A', 'Z');
        $size = count($letters);

        // Loop through and add random letters
        for($i = 0; $i < $passes; $i++)
        {
            // Create some random X and Y's to a random letter placement
            $X = mt_rand(1, $w);
            $Y = mt_rand(1, $h);
            
            // Random amout of letters created this pass.
            $amount = mt_rand(1, 10);
        
            // Now loop through the amount of letters this pass.
            for($ii = 0; $ii < $amount; $ii++)
            {
                // Get a random letter to add
                $letter  = $letters[mt_rand(0, $size - 1)];

                // Add the letter to the image
                imagettftext(
                    $image, // Image Source
                    25, // Font Size
                    mt_rand(-15, 15), // Angles
                    $X + mt_rand(-50, 50), // X
                    $Y + mt_rand(-50, 50), // Y
                    $fontcolor, // Font Color
                    $font, // Font File
                    $letter // Letter
                );
        
            }
        
        }

    }
} 
?>