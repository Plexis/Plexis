<?php
// Required for the sleep function to work properly
set_time_limit(0);

// Define a smaller Directory seperater and ROOT, Plexis SYSTEM paths
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('PLEXIS_PATH', realpath(ROOT . DS .'../'));
define('SYSTEM_PATH', realpath(ROOT . DS .'../system'));
define('CACHE_FILE', SYSTEM_PATH . DS . 'cache' . DS . 'debugger.cache');

// Make sure this is an ajax request only!
if( !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'))
{
    die('No Direct Access');
}

// Make sure we have a post action
if(!isset($_POST['action'])) die('No Direct Linking!');

// Include a few needed files
include ROOT . DS .'inc'. DS .'Functions.php';
include SYSTEM_PATH . DS .'library'. DS .'Cache.php';

// Load our cache class
$Cache = new Library\Cache();
$Cache->set_path( dirname( CACHE_FILE ) );
$data = $Cache->get('debugger');
if(!is_array($data))
    output( array(
        'success' => false,
        'finished' => true,
        'data' => 'Cache file doesnt exist, or is expired'
    ));

// Build our output array
$output = array(
    'success' => true,
    'finished' => false,
    'data' => null
);

// Check to make sure the script is running first
if($data['flags'] > 0)
{
    $output['finished'] = true;
}
else
{

    // Preform the request
    switch($_POST['action'])
    {
        case 'get':
            $data['variable'] = $_POST['data'];
            $data['variable_mode'] = 'get';
            $Cache->save('debugger', $data, 2);
            sleep(2);
            $contents = $Cache->get('debugger');
            $output['data'] = highlight($contents['output']);
            break;
            
        case 'set':
            $data['variable'] = $_POST['variable'];
            $data['variable_mode'] = 'set';
            $data['variable_in'] = array(
                'type' => typeToString($_POST['type']),
                'value' => $_POST['value']
            );
            $Cache->save('debugger', $data, 2);
            sleep(2);
            $contents = $Cache->get('debugger');
            $output['data'] = highlight($contents['output']);
            break;
            
        case 'kill':
            $data['flags'] = 2;
            $Cache->save('debugger', $data, 2);
            break;
            
        case 'next':
            $data['next_step'] = 1;
            $Cache->save('debugger', $data, 2);
            usleep(1500000);
            $contents = $Cache->get('debugger');
            if($contents['next_step'] == 0)
            {
                if($contents['flags'] == 2)
                {
                    $output['finished'] = true;
                }
                else
                {
                    $output['data'] = array(
                        'script' => basename($contents['file']),
                        'file' => SYSTEM_PATH. DS . $contents['file'],
                        'line' => $contents['line'],
                        'file_contents' => getFileContents($contents['file'], $contents['line'])
                    );
                }
            }
            break;
            
        case 'finish':
            $data['flags'] = 1;
            $Cache->save('debugger', $data, 2);
            break;
            
        case 'status':
            $output['data'] = $data;
            $out = $data['output'];
            if($out != null)
            {
                $data['output'] = null;
                $Cache->save('debugger', $data, 2);
                $output['data']['output'] = highlight($out);
            }
            break;
            
        case 'getFile':
            $output['data'] = getFileContents($data['file'], $data['line']);
            break;
    }
}

echo output($output);