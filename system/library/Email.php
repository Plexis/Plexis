<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author: 		Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Email()
| ---------------------------------------------------------------
|
| A class built to send emails
|
*/
namespace System\Library;

class Email 
{
    // Our email recipient
    protected $to = '';
    
    // An Array of carbon copy's
    protected $cc = array();
    
    // An array of blind carbon copy's
    protected $bcc = array();
    
    // Our email subjecy
    protected $subject = '';
    
    // The email message
    protected $message = '';
    
    // An array of attachment data
    protected $attachment = array();
    
    // Email Character Set
    protected $charset = 'ISO-8859-1';
    
    // Email Boundary
    protected $boundary = '';
    
    // Email header data
    protected $header = '';
    
    // For our message so we can combine text and html alternatives
    protected $textheader = '';
    
    // An array of errors
    public $errors = array();

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/     
    public function __construct() 
    {
        // Set our email boundary
        $this->boundary = uniqid(time());
    }
    
/*
| ---------------------------------------------------------------
| Function: send()
| ---------------------------------------------------------------
|
| Calling this method will build the email and send it
|
| @Param: (Bool) $supress - Supress errors?
| @Return: (Bool) returns TRUE if sent, FALSE otherwise
|
*/    
    public function send($supress = true) 
    {
        // Build the email header
        $this->build_header();
        
        // Disable error reporting
        if($supress = true) load_class('Debug')->silent_mode(true);
        
        // Send the email
        $sent = mail($this->to, $this->subject, $this->message, $this->header);
        
        // Re-enable errors and return
        if($supress = true) load_class('Debug')->silent_mode(false);
        return $sent;
    }

/*
| ---------------------------------------------------------------
| Function: to()
| ---------------------------------------------------------------
|
| Adds a recipient to our email
|
| @Param: (String) $email - the email address we are sending to
| @Return: (Bool) returns TRUE
|
*/ 
    public function to($email, $name = NULL) 
    { 
        // Check if the email is valid before adding it
        if( !$this->validate($email) ) return FALSE;
        
        // Email is valid at this point
        if( $name == NULL )
        {
            $this->to = $email;
        }
        else
        {
            $this->to = $name." <".$email.">";
        }
        return TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: from()
| ---------------------------------------------------------------
|
| Adds a sender to our email
|
| @Param: (String) $email - the email address we are sending from
| @Return: (Bool) returns TRUE
|
*/ 
    public function from($email, $name = NULL) 
    { 
        // Check if the email is valid before adding it
        if( !$this->validate($email) ) return FALSE;
        
        // Email is valid at this point
        if( $name == NULL )
        {
            $this->header .= "From: ".$email."\r\n";
        }
        else
        {
            $this->header .= "From: ".$name." <".$email.">\r\n";
        }
        return TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Function: reply_to()
| ---------------------------------------------------------------
|
| Adds a reply to email
|
| @Param: (String) $email - the email address we are sending from
| @Return: (Bool) returns TRUE
|
*/ 
    public function reply_to($email, $name = NULL) 
    { 
        // Check if the email is valid before adding it
        if( !$this->validate($email) ) return FALSE;
        
        // Email is valid at this point
        if( $name == NULL )
        {
            $this->header .= "Reply-to: ".$email."\r\n";
        }
        else
        {
            $this->header .= "Reply-to: ".$name." <".$email.">\r\n";
        }
        return TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: cc()
| ---------------------------------------------------------------
|
| Adds a CC to our email
|
| @Param: (String) $email - the email address we are adding
| @Return: (Bool) returns TRUE
|
*/    
    public function cc($email) 
    {
        // Check if the email is valid before adding it
        if( !$this->validate($email) ) return FALSE;
        
        // Email is valid at this point, add it and return TRUE
        $this->cc[] = $email;
        return TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: bcc()
| ---------------------------------------------------------------
|
| Adds a BCC to our email
|
| @Param: (String) $email - the email address we are adding
| @Return: (Bool) returns TRUE
|
*/    
    public function bcc($email) 
    { 
        // Check if the email is valid before adding it
        if( !$this->validate($email) ) return FALSE;
        
        // Email is valid at this point, add it and return TRUE
        $this->bcc[] = $email;
        return TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: subject()
| ---------------------------------------------------------------
|
| Sets the emails subject line
|
| @Param: (String) $subject - the email's subject
| @Return: (Bool) returns TRUE
|
*/    
    public function subject($subject) 
    {
        $this->subject = strip_tags(trim($subject));
        return TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: message()
| ---------------------------------------------------------------
|
| This method adds the message to the headers, so we can send
| a plaing text AND/OR html version as well, just using php's
| built in mail function
|
| @Param: (String) $message - the in either html / text format
| @Return: (None)
|
*/    
    public function message($message = '', $type = 'html') 
    {
        $textboundary = uniqid('textboundary');
        $this->textheader = "Content-Type: multipart/alternative; boundary=\"".$textboundary."\"\r\n\r\n";
        $this->message .= "--". $textboundary ."\r\n";
        $this->message .= "Content-Type: text/plain; charset=\"". $this->charset ."\"\r\n";
        $this->message .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $this->message .= strip_tags($message) ."\r\n\r\n";
        $this->message .= "--". $textboundary ."\r\n";
        $this->message .= "Content-Type: text/html; charset=\"".$this->charset ."\"\r\n";
        $this->message .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $this->message .= $message ."\r\n\r\n";
        $this->message .= "--". $textboundary ."--\r\n\r\n";
    }

/*
| ---------------------------------------------------------------
| Function: attachment()
| ---------------------------------------------------------------
|
| Adds an attachment to the email
|
| @Param: (String) $file - the file path for the file we are sending
| @Return: (Bool) returns FALSE on failure
|
*/    
    public function attachment($file) 
    {
        // Make sure we are dealing with a real file here
        if(is_file($file)) 
        {
            $basename = basename($file);
            $attachmentheader = "--". $this->boundary ."\r\n";
            $attachmentheader .= "Content-Type: ".$this->mime_type($file)."; name=\"".$basename."\"\r\n";
            $attachmentheader .= "Content-Transfer-Encoding: base64\r\n";
            $attachmentheader .= "Content-Disposition: attachment; filename=\"".$basename."\"\r\n\r\n";
            $attachmentheader .= chunk_split(base64_encode(fread(fopen($file,"rb"),filesize($file))),72)."\r\n";
            $this->attachment[] = $attachmentheader;
        } 
        else 
        {
            // Not a file
            return FALSE;
        }
    }

/*
| ---------------------------------------------------------------
| Function: build_header()
| ---------------------------------------------------------------
|
| This method builds the emails header before being sent
|
*/    
    protected function build_header() 
    {
        // Add out Cc's
        $count = count($this->cc);
        if($count > 0) 
        {
            $this->header .= "Cc: ";
            for($i=0; $i < $count; $i++) 
            {
                // Add comma if we are not on our first!
                if($i > 0) $this->header .= ',';
                $this->header .= $this->cc[$i];
            }
            $this->header .= "\r\n";
        }
        
        // Add out Bcc's
        $count = count($this->bcc);
        if($count > 0) 
        {
            $this->header .= "Bcc: ";
            for($i=0; $i < $count; $i++) 
            {
                // Add comma if we are not on our first!
                if($i > 0) $this->header .= ',';
                $this->header .= $this->bcc[$i];
            }
            $this->header .= "\r\n";
        }
        
        // Add our MINE version and X-Mailer
        $this->header .= "X-Mailer: Frostbite Framework\r\n";
        $this->header .= "MIME-Version: 1.0\r\n";
        
        // Add attachments
        $attachcount = count($this->attachment);
        if($attachcount > 0) 
        {
            $this->header .= "Content-Type: multipart/mixed; boundary=\"". $this->boundary ."\"\r\n\r\n";
            $this->header .= "--". $this->boundary ."\r\n";
            $this->header .= $this->textheader;

            if($attachcount > 0) $this->header .= implode("", $this->attachment);
            $this->header .= "--". $this->boundary ."--\r\n\r\n";
        } 
        else 
        {
            $this->header .= $this->textheader;
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: validate()
| ---------------------------------------------------------------
|
| This method determines if the string is a valid email
|
| @Param: (String) $email - the emailwe are checking
| @Return: (Bool) TRUE if the field is an email, FALSE otherwise
|
*/    
    public function validate($email) 
    {
        // Use PHP's built in email validator
        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $this->errors[] = "Invalid Email: <". $email .">";
            return FALSE;
        }
        return TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: mime_type()
| ---------------------------------------------------------------
|
| Gets the mime type of a file for attachments
|
| @Param: (String) $file - the file we are checking
| @Return: (String) returns the file's mime type
|
*/    
    public function mime_type($file) 
    {
        $finfo = new finfo();
        return $finfo->file($file, FILEINFO_MIME);
    }

/*
| ---------------------------------------------------------------
| Function: clear()
| ---------------------------------------------------------------
|
| Clears the current email
|
*/    
    public function clear()
    {
        $this->header = NULL;
        $this->to = NULL;
        $this->subject = NULL;
        return TRUE;
    }
}
// EOF