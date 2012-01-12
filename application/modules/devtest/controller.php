<?php
class Devtest extends \Application\Core\Controller 
{
    public function __construct($construct = TRUE)
    {
        if($construct == TRUE)
        {
            parent::__construct();
        }
        else
        {
            // Build your own construct
            $this->load = load_class('Loader');
            $this->DB = $this->load->database( 'DB' );
        }
    }

/*
| ---------------------------------------------------------------
| Install and Uninstall running functions
| ---------------------------------------------------------------
*/
    public function _install()
    {
        $this->DB->forge->add_field('id');
        $this->DB->forge->add_field('test');
        $this->DB->forge->add_key('id');
        return $this->DB->forge->create_table('test', FALSE);
    }
    
    public function _uninstall()
    {
        return $this->DB->forge->drop_table('test');
    }
    
    public function has_admin()
    {
        return TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Page Functions
| ---------------------------------------------------------------
*/
    public function index($mode = 1) 
    {
        // $this->load->library('Soap');
        // $check = $this->Soap->connect('127.0.0.1', 7878, 'wilson212', 'facelift6');
        // if($check)
        // {
            // $this->Soap->send('.send mail hudson "test" "test"');
            // echo $this->Soap->get_response();
            // $this->Soap->disconnect();
        // }
        if($mode == 2)
        {
            echo "Task: Rename Table `test` to `works`<br />Result: ";
            print_r( (int)$this->DB->forge->rename_table('test', 'works') );
        }
        elseif($mode == 3)
        {
            echo "Task: Add column `worked` to  Table `works`<br />Result: ";
            print_r( (int)$this->DB->forge->add_column('works', 'worked', 'int', '5') );
        }
        elseif($mode == 4)
        {
            echo "Task: Drop column `worked` to  Table `works`<br />Result: ";
            print_r( (int)$this->DB->forge->drop_column('works', 'worked') );
        }
        elseif($mode == 5)
        {
            echo "Task: Drop Table `works`<br />Result: ";
            print_r( (int)$this->DB->forge->drop_table('works') );
        }
        else
        {
            echo "Task: Add Table `test`<br />Result: ";
            $this->DB->forge->add_field('id');
            $this->DB->forge->add_field('test');
            $this->DB->forge->add_key('id');
            print_r( (int)$this->DB->forge->create_table('test', FALSE) );
        }
        $url = SITE_URL ."/devtest/index";
        echo "<br /><br />Go to step <a href='$url/2'>2</a> - <a href='$url/3'>3</a> - <a href='$url/4'>4</a> - <a href='$url/5'>5</a> - <a href='$url/1'>1</a>";
        //$this->load->view('index');
    }
    
    public function test()
    {
        $this->load->view('index');
    }
}
?>