<?php
/**
 * This file contains utility functions
 * 
*/

function get_file_path_for_slug($slug) {
    global $site; // Access the global site configuration
    foreach ($site['ext'] as $ext) {
        $file_path = join_path($site['dir'],'content', str_replace('/', DS, $slug) . '.' . $ext);
        if(file_exists($file_path)) {
            return $file_path; // Return the first matching file
        }


        $file_path = join_path($site['dir'],'content', str_replace('/', DS, $slug), 'index.'.$ext);
        if(file_exists($file_path)) 
        {   
            return $file_path; // Return the first matching file
        }

    }
    return false; // No matching file found
}


function join_path(...$paths) {
    $jp =  implode(DIRECTORY_SEPARATOR, array_map(function($path) {
        $path = preg_replace('/[\\\\\/]+/',DIRECTORY_SEPARATOR, $path);
        return $path;
    }, $paths));

    return preg_replace('/[\\\\\/]+/',DIRECTORY_SEPARATOR, $jp);
}

function format_slug($slug)
{
    $slug = str_replace('index.php',"",$slug);
    $slug = preg_replace('/\/+/', '/', $slug); // Replace multiple slashes with a single one
    $slug = str_replace(DS, "/",$slug);
    # Slug to have leading slash but not trailing slash
    $slug = "/".trim($slug, "/");
    return $slug ?: '/'; // Ensure slug is never empty
}


//Remove query parameter from the URL
function remove_query_parameters($url) {
    // Find the position of the query string start
    $queryStartPos = strpos($url, '?');
    
    // If there is no query string, return the original URL
    if ($queryStartPos === false) {
        return $url;
    }
    
    // Extract the part of the URL before the query string
    $cleanUrl = substr($url, 0, $queryStartPos);
    
    // Optionally include the fragment if it exists
    $fragmentStartPos = strpos($url, '#', $queryStartPos);
    if ($fragmentStartPos !== false) {
        $cleanUrl .= substr($url, $fragmentStartPos);
    }
    
    return $cleanUrl;
}

# Recursively remove directory
function rrmdir($dir) {
    global $site;
    $real_dir = realpath($dir);
    // Security check: Ensure $dir is a subdirectory of $parentDir
    if (strpos($real_dir, $site['dir']. DIRECTORY_SEPARATOR) !== 0) {
        return false; // Not a subdirectory
    }

   if (is_dir($dir)) { 
     $objects = scandir($dir);
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
           rrmdir($dir. DIRECTORY_SEPARATOR .$object);
         else
           unlink($dir. DIRECTORY_SEPARATOR .$object); 
       } 
     }
     rmdir($dir); 
   } 
 }

# Return hash for a slug
 function slug_hash($slug)
{
    return hash('sha1',$slug);
}

# Return hash for a directory
function dir_hash($dir_path)
{
     if (!is_dir($dir_path)) {
            return false;
    }

    $files = glob($dir_path . '/*'); // Get all files and subdirectories
    $hash_data = '';

    foreach ($files as $file) {
        $hash_data .= is_file($file) ? (filemtime($file) . filesize($file)) : dir_hash($file);
    }
    return hash('sha256', $hash_data); // Hash only metadata for speed
}


function rel_url($path) {
    global $site;

    $url = $site['base']."/".$path;
    $url = preg_replace('/\/+/', '/', $url);
    return $url;

}
