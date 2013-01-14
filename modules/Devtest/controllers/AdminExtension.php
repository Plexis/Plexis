<?php
/*
| ---------------------------------------------------------------
| Example Module Admin Controller
| ---------------------------------------------------------------
*/
namespace Devtest;

class AdminExtension
{

/*
| ---------------------------------------------------------------
| Required Install and Uninstall Methods
| ---------------------------------------------------------------
*/
    // This function is ran when the user installs the module via the admin panel
    // Return TRUE if the module installs correctly, or false
    public function install()
    {
        return true;
    }
    
    // This function is ran when the user Un-installs the module via the admin panel
    // Return TRUE if the module un-installs correctly, or false
    public function uninstall()
    {
        return true;
    }
}