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
        
        // Make sure the realm exists
        $query = "SELECT * FROM `pcms_realms` WHERE `id`=?";
        $result = $this->DB->query( $query, array($id) )->fetch_row();
        
        // Check for FALSE
        if( $result == FALSE )
        {
            output_message('error', 'invalid_realm_id');
            $this->load->view('blank');
            return;
        }
        
        // Load the WoWLib
        $this->load->wowlib($id, 'wowlib');
        $start = 50 * $page;
        $data['characters'] = $this->wowlib->get_online_list(50, $start);
        
        // Loop through each player and add the
        if( !empty($data['characters']) )
        {
            foreach($data['characters'] as $key => $value)
            {
                $g = $value['gender'];
                $r = $value['race'];
                $race = $this->wowlib->race_to_text($r);
                $class = $this->wowlib->class_to_text($value['class']);
                $zone = $this->wowlib->zone_to_text($value['zone']);
                $data['characters'][$key]['race'] = '<img src="'. SITE_URL .'/application/static/images/icons/race/'. $r .'-'. $g .'.gif" title="'.$race.'" alt="'.$race.'">';
                $data['characters'][$key]['class'] = '<img src="'. SITE_URL .'/application/static/images/icons/class/'. $value['class'] .'.gif" title="'.$class.'" alt="'.$class.'">';
                $data['characters'][$key]['zone'] = $zone;
            }
        }
        
        // Load the view and call it a day!
        $this->load->view('onlinelist', $data);
    }

}
// EOF