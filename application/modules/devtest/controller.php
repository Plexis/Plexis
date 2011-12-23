<?php
class devtest extends Application\Core\Controller 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index() 
	{
        $this->load->library('Soap');
        $check = $this->Soap->connect('127.0.0.1', 7878, 'wilson212', 'facelift6');
        if($check)
        {
            $this->Soap->send('.send mail hudson "test" "test"');
            echo $this->Soap->get_response();
            $this->Soap->disconnect();
        }
		// $this->load->view('index', $data);
	}
}
?>