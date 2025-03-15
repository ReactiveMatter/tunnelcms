<?php
require 'core/vendor/autoload.php'; // Include the Composer autoloader for Parsedown

ini_set('display_errors', 1);    // Turn on displaying errors
error_reporting(E_ALL);          // Report all types of errors

if (!file_exists('config.php'))
{
    exit("Configuration error");
}
else
{
    require 'config.php';
}

/* Load required files */
require_once "core/include/cache.php";
require_once "core/include/utils.php";
require_once "core/include/build.php";

mb_internal_encoding('UTF-8');
define('DS', DIRECTORY_SEPARATOR );

# Set site HTTP base and directory path
$site['base'] = str_replace(DS,'/', str_replace($_SERVER['DOCUMENT_ROOT'], '',  str_replace(DS,'/',dirname(__DIR__))));
$site['dir'] = dirname(__DIR__);

# The requested slug
$request['slug']=str_replace($site['base'],'',remove_query_parameters($_SERVER['REQUEST_URI']));
$request['slug']=str_replace('content/','',$request['slug']);
$request['slug']=format_slug($request['slug']);

# Load plugins
require_once "core/include/plugin_manager.php";

run_hook('start');



# Check if file exists for requested slug
$request['filepath'] = get_file_path_for_slug($request['slug']);
$request['hashes']['content'] = dir_hash(join_path($site['dir'], 'content'));
$request['hashes']['template'] = dir_hash(join_path($site['dir'], 'template'));
$request['hashes']['slug'] = slug_hash($request['slug']);

# Load 404 file or 404 header if file doesn't exist
if(!$request['filepath'])
{   

    if(get_file_path_for_slug('404'))
    {   
        $request['filepath'] = get_file_path_for_slug('404');
        $request['hashes']['slug'] = slug_hash('404');
        http_response_code(404); 
    }
    else
    {
        header("HTTP/1.0 404 Not Found");
        exit("HTTP/1.0 404 Not Found");
    }
    
}
    


# Remove extra slashes.
 $request['filepath'] = preg_replace('#/{2,}#', '/',  $request['filepath']);  // For forward slashes
 $request['filepath'] = preg_replace('#\\\\{2,}#', '\\',  $request['filepath']); // For backslashes

# Check if cache valid
if(isset($site['cache']) && $site['cache'] && cache_valid($request))
{
    #If cache enabled and cache is valid
    serve_cache($request);
}
else if(isset($site['cache']) && $site['cache'] && !cache_valid($request))
{   
    #If cache enabled and cache is invalid

    # Parse file and build $page
    $page = build($request);
    # Output HTML generated using template. It adds $page['html'] and echoes it
    ob_start();
    run_hook('before_render');
    render($page);
    $output = ob_get_contents();
    ob_flush();

    run_hook('after_render');
    $page['html'] = $output;

    # Create cache for page
    create_cache($page);
}
else
{   
    #If cache not enabled
    
    $page = build($request);
    run_hook('before_render');
    render($page);
    run_hook('after_render');
}

run_hook('end');