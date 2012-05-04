<?php
/*
| ---------------------------------------------------------------
| Environment
| ---------------------------------------------------------------
|
| This is the error level of which you would like to show when
| viewing the website. This should be set to 2 (all errors + debugging) 
| when developing or testing, and just 1 (only fetal erros, no debugging) 
| for live sites.
|
| Levels:
| 	2 = Development, Set at this level when all errors are to be displayed in detail
|	1 = Production, Enabled when your site is live on the web
*/

$config['environment'] = 2;


/*
| ---------------------------------------------------------------
| Log_errors
| ---------------------------------------------------------------
|
| Set to 1 to log errors in the error log. Set to 0 to disable
| error logging.
|
*/

$config['log_errors'] = 1;

/*
| ---------------------------------------------------------------
| Catch_fetal_errors
| ---------------------------------------------------------------
|
| Set to 1 to catch fetal errors and parse errors. Best to disable
| if already using a special debugger such as Xdebug
|
*/

$config['catch_fetal_errors'] = 1;


/*
| ---------------------------------------------------------------
| Core_language
| ---------------------------------------------------------------
|
| The language folder in system/language/ <language> / that you
| wish to use for error reporting
|
*/

$config['core_language'] = 'english';


/*
| ---------------------------------------------------------------
| Default_controller
| ---------------------------------------------------------------
|
| This is the default controller that loads when no path is givin
|
*/

$config['default_controller'] = 'welcome';


/*
| ---------------------------------------------------------------
| Default_controller
| ---------------------------------------------------------------
|
| This is the default action that loads when no action is givin
|
*/
$config['default_action'] = 'index';



/// =====[ PlexisCMS Users ] DONOT Edit Below This Line! ===== ///

/*
| ---------------------------------------------------------------
| Qeury Strings
| ---------------------------------------------------------------
|
| 'enable_query_strings' - When enabled, you can use a normal query 
|   string using the set triggers ( example.com/?c=controller&m=function ),
|   addition params in the query string are passed in order to the
|   functions arguments. by default (FALSE), Frostbite will use pretty
|   urls ( example.com/controller/action/param1/param2 ). Servers lacking 
|   mod_rewrite support will need this set to TRUE
|
*/
$config['enable_query_strings'] = FALSE;
$config['controller_param'] = 'c';
$config['action_param'] = 'm';


/*
| ---------------------------------------------------------------
| Auto load Libraries
| ---------------------------------------------------------------
|
| These are the classes located in the core/libraries folder
| or in your application/libraries folder. Use the format below
| to define which librarys are loaded. Donot prefix the classes
| as the prefixed classes will load automatically
|
| Format: array('Session', 'Database', 'Parser');
|
*/

$config['autoload_libraries'] = array('Auth', 'Template', 'Statistics');


/*
| ---------------------------------------------------------------
| Helpers
| ---------------------------------------------------------------
|
| These are the helper files located in the core/helpers folder
| or in your application/helpers folder.
|
| Format: array('helper_file', 'helper_file');
|
*/

$config['autoload_helpers'] = array('plexis');


/*
| ---------------------------------------------------------------
| Session: Use Database
| ---------------------------------------------------------------
|
| When useing the session class, do we allow session to be saved
| in the database ( for "Remeber Me's" ). NOTE, you must run
| the session_table.sql on your DB for this to be enabled!
|
| Format: TRUE or FALSE;
|
*/

$config['session_use_database'] = TRUE;


/*
| ---------------------------------------------------------------
| Session: Database Identifier
| ---------------------------------------------------------------
|
| Which Database is the Session Table located in? NOTE, you must
| have " $config['session_use_database'] " above Set to TRUE.
|
| Format: either numeric ( id in array ), or DB config array Key.
|
*/

$config['session_database_id'] = 'DB';


/*
| ---------------------------------------------------------------
| Session: Table Name
| ---------------------------------------------------------------
|
| Which is the session table name? NOTE, you must 
| " $config['session_use_database'] " above Set to TRUE.
|
| Format: String - Table name
|
*/

$config['session_table_name'] = 'pcms_sessions';


/*
| ---------------------------------------------------------------
| Session: Cookie Name
| ---------------------------------------------------------------
|
| Name of the cookie we are storing session information in
|
| Format: String - Cookie name
|
*/

$config['session_cookie_name'] = 'session';

// EOF