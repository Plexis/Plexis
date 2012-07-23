    Each folder in this directory is an individual wowlib for a particular revision of a core. A wowlib
is a group of classes, that contain a list of functions that preform specific tasks on the world and 
character databases. The point of a wowlib, is to make the cms easy to work with ANY emulator / core revision.
With a wowlib, it is even possible to have both old and new revisions of a core, working without errors within
the cms. Most wowlibs span between mulitple revisions! 

** Each wowlib is required to have 3 extensions as listed below:

    * Characters.php    - Contains functions pretaining to handling the Characters database
    * World.php         - Contains functions pretaining to handling the World database
    * Zone.php          - Contains functions pretaining to Zone ID's
    
    * NOTE: Any additional files will be loaded as custom extensions, and are used just like normal extensions:
        $this->wowlib->{extension}->{method}();
    
    The only time you will need to create a new wowlib, is if you experience errors when using the default wowlib.
It is better to create a whole new wowlib, then to edit the default one!!! This way you can still use the old later.
If you are creating a new wowlib, its best to just copy the files located in the "_default" wowlib folder, and paste
them into a new folder within this directory, and edit the new files to fix the errors. The wowlib will automatically
show in the "Manage Realms" screen in the admin panel. NOTE: You will need to change the namespace in each of the 
copied files to match the wowlib name you are creating (IE: "namespace Wowlib\<my_wow_lib_folder_name>;").

    Each wowlib file must have the same functions as the extensions in the "_default" wowlib, as the cms calls these functions.
Any missing functions will cause errors! All wowlib class FILE names must be capitalized, as well as the class name within
each extension. Failure to do so will also cause errors (specifically on Linux servers)