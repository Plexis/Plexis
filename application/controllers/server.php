<?php
class Server extends Application\Core\Controller 
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index() 
    {
        redirect('server/onlinelist');
    }
    
    public function onlinelist($id = 0, $page = 1) 
    {
        // Get our realm id if none is provieded
        if($id == 0)
        {
            $id = get_realm_cookie();
            
            // If $id still equals 0, then no realms are installed!
            if($id == 0)
            {
                output_message('info', 'no_realms_installed');
                $this->load->view('blank');
                return;
            }
        }
        
        // Load the view and call it a day!
        $this->load->view('onlinelist', array('realm_id' => $id));
    }

}
// EOF