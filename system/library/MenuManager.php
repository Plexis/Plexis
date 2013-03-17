<?php
namespace Library;

class MenuManager
{
    protected $DB;
    protected $rows = array();
    protected $indent = 1;
    
    public function __construct($id)
    {
        // Fetch the menu from the database
        $this->DB = \Plexis::LoadDBConnection();
        
        // Get menu items
        $query = "SELECT `id`, `label`, `url`, `img`, `class`, `parent`, `position` FROM `pcms_menus` 
            WHERE `menu_id`='$id' ORDER BY `parent`, `position`;";
        $rows = $this->DB->query($query)->fetchAll();
        
        // Throw excpetion if the menu doesnt exist
        if($rows == false)
            throw new \Exception("Menu '$id' does not exist");
        
        // Add the rows to our data, sorting by parent ID
        foreach($rows as $row)
            $this->rows[$row['parent']][] = $row;
    }
    
    public static function Build($id, $attrs = array())
    {
        $class = new MenuManager($id);
        return $class->toHtml($attrs);
    }
    
    public function toHtml($attrs = array()) 
    {
        $ul = null;
        foreach($attrs as $k => $a)
            $ul .= "{$k}=\"{$a}\" ";
        return $this->generate(0, rtrim($ul));
    }
    
    public function save($json) {}
    
    protected function generate($parent, $attr)
    {
        // Return false for now child menu's
        if(!isset($this->rows[$parent])) 
            return false;
        
        // Create our indent
        $indent = str_repeat("\t", $this->indent);
        if($attr != null)
            $attr = ' '. $attr;
        
        // Start ordered list
        $html = "\n". $indent;
        $html .= "<ul$attr>";
        ++$this->indent;
        foreach($this->rows[$parent] as $row) 
        {
            // Add new row
            $html .= "\n\t". $indent;
            $html .= '<li'. (($row['class'] != null) ? ' class="'. $row['class'] .' sortable">' : '>');
            $html .= '<a href="'. $row['url'] .'">';
            
            // Add image
            if(!empty($row['img']))
                $html .= "<img src=\"". $row['img'] ."\">";
            
            // Add label
            $html .= $row['label'];
            $html .= "</a>";
            
            // Add child menu's
            if(($child = $this->generate($row['id'], 'class="sub"')) != false)
            {
                --$this->indent;
                $html .= $child;
                $html .= "\n\t". $indent;
            }
            
            // Close the row
            $html .= '</li>';
        }

        return $html ."\n$indent</ul>";
    }
}