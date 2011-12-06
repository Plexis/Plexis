<?php
class devtest extends Application\Core\Controller 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index() 
	{	
		$this->load->view('index');
	}
}
?>