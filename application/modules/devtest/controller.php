<?php
class devtest extends Application\Core\Controller 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index() 
	{
        // Load the WoWLib
        $lib = $this->load->wowlib(1);
        $data['characters'] = $lib->list_characters();
        
        // Added the images / text etc
        foreach($data['characters'] as $key => $value)
        {
            $g = $value['gender'];
            $r = $value['race'];
            $race = $lib->race_to_text($r);
            $class = $lib->class_to_text($value['class']);
            $zone = $lib->zone_to_text($value['zone']);
            $data['characters'][$key]['race'] = '<img src="'. SITE_URL .'/application/static/images/icons/race/'. $r .'-'. $g .'.gif" title="'.$race.'" alt="'.$race.'">';
            $data['characters'][$key]['class'] = '<img src="'. SITE_URL .'/application/static/images/icons/class/'. $value['class'] .'.gif" title="'.$class.'" alt="'.$class.'">';
            $data['characters'][$key]['zone'] = $zone;
        }
		$this->load->view('index', $data);
	}
}
?>