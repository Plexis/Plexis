    A wowlib is best described as a "go between" or a connection between the cms, and the WoW
server databases. The cms calls upon the requested action to be preformed (function), and the
wowlib process the request best suited to the current revision of the core.
    
    Each folder in this directory is an emulator. Each emulator must contain at least 1 wowlib
(see README.txt in any of the current emulator folders). Failure to provide a "_default" wowlib,
will cause the cms errors. All emulators in this directory, will automatically be listed as a
selectable emulator in the admin panel of this cms. Each wowlib in the emulator folders is optimized
for a certain span of core revisions... Whenever a core update happens, that changes a table
for example, a new updated wowlib will need to be created so the cms will still be able to do
the requested actions without error.

    Each emulator, must contain a "realm" class ( <emulator>/<Emulator>.php ). The realm class is
used to provide basic account actions such as verifying login information, banning accounts, and 
changing an account password / email. The realm class needs to be namespaced in the "Wowlib" namespace. 
To get an idea of what you need to do to add a custom emulator, its best to just copy an emulator in 
this directory, and use it as a template to make your own.