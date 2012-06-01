<?php
class devtest_Model extends System\Core\Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function get_page_contents()
	{
		$contents = "This is a test";
		return $contents;
	}
}
// EOF