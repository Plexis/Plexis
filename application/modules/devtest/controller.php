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
        $table = $this->DB->forge->create_table('test');
        $table->add_column('id', 'int', 15, array('primary' => TRUE, 'increments' => TRUE, 'unsigned' => TRUE));
        $table->add_column('test', 'string', 100, array('default' => 'test'));
        return $table->execute();
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
            echo ($this->DB->forge->rename_table('test', 'works') == TRUE) ? 'Success' : 'Failed';
        }
        elseif($mode == 3)
        {
            $table = $this->DB->forge->alter_table('works');
            $table->add_column('worked', 'string', 255, array('default' => 'alter add works!'));
            echo "Task: Add column `worked` to  Table `works`<br />Result: ";
            echo ($table->execute() == TRUE) ? 'Success' : 'Failed';
        }
        elseif($mode == 4)
        {
            $table = $this->DB->forge->alter_table('works');
            $table->drop_column('test2');
            echo "Task: Drop column `worked` to  Table `works`<br />Result: ";
            echo ($table->execute() == TRUE) ? 'Success' : 'Failed';
        }
        elseif($mode == 5)
        {
            echo "Task: Drop Table `works`<br />Result: ";
            echo ($this->DB->forge->drop_table('works') == TRUE) ? 'Success' : 'Failed';
        }
        else
        {
            $table = $this->DB->forge->create_table('test');
            $table->add_column('id', 'int', 15, array('primary' => TRUE, 'increments' => TRUE, 'unsigned' => TRUE));
            $table->add_column('test', 'string', 100, array('default' => 'test'));
            echo "Task: Add Table `test`<br />Result: ";
            echo ($table->execute() == TRUE) ? 'Success' : 'Failed';
        }
        
        $url = SITE_URL ."/devtest/index";
        echo "<br /><br />Go to step <a href='$url/2'>2</a> - <a href='$url/3'>3</a> - <a href='$url/4'>4</a> - <a href='$url/5'>5</a> - <a href='$url/1'>Back to Start</a>";
        //$this->load->view('index');
    }
    
    public function test()
    {
        $this->load->view('index');
    }
}
?>