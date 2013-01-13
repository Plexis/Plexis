<?php
    function setPageTitle($title)
    {
        Library\Template::SetVar('page_title', $title);
    }
    
    function setPageDesc($desc)
    {
        Library\Template::SetVar('page_desc', $desc);
    }