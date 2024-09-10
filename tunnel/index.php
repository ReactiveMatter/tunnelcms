<?php
require 'vendor/autoload.php'; // Include the Composer autoloader for Parsedown
require 'config.php';

use Symfony\Component\Yaml\Yaml;

mb_internal_encoding('UTF-8');

if (!file_exists('config.php'))
{
    echo "Configuration error";
    exit();
} 

define('DS', DIRECTORY_SEPARATOR );
// Get the document root
$root = $_SERVER['DOCUMENT_ROOT'];

// Get the content directory
$site_dir = dirname(__DIR__);
$site['base'] = str_replace(DS,'/', str_replace($root, '',  str_replace(DS,'/',($site_dir))));
$tunnel_dir = __DIR__;
$parsedown = new Parsedown();


/* Setting default values if not provided in config */
if(!isset($site['ext']))
{   
    $site['ext'] = ['md'];
}
if(!isset($site['date-format']))
{
    $site['date-format'] = "Y-m-d";
}
if(!isset($site['default-title']))
{   
    $site['default-title'] = "Untitled";
}
if(!isset($site['default-layout']))
{   
    $site['default-layout'] = "default";
}
if(!isset($site['build-drafts']))
{   
    $site['build-drafts'] = false;
}

$db = new SQLite3('build.db');

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS cache_table (
    slug TEXT NOT NULL PRIMARY KEY UNIQUE,
    mtime DATETIME NOT NULL,
    btime DATETIME NOT NULL
);
SQL;

// Execute the SQL statement
$result = $db->exec($sql);

$pages = [];


function join_path(...$paths) {
    return implode(DIRECTORY_SEPARATOR, array_map(function($path) {
        return trim($path, DIRECTORY_SEPARATOR);
    }, $paths));
}

function format_slug($slug)
{
    $slug = str_replace(DS, "/",$slug);
    $slug = trim($slug, "/")."/";
    return $slug;
}

function add_base_to_links($matches) {
    global $site;
    $url = $matches[2]; // Extract the URL part
    if(str_starts_with($url, '$/'))
    {
        $url = rtrim($site['base'], "/")."/".ltrim($url,'$/');
    }
    return '['.$matches[1] .']'. '(' . $url . ')'; // Return modified link
}

// Function to serve a Markdown file as HTML
function parse($file) {

    global $parsedown, $site, $site_dir, $tunnel_dir;

    $ext = pathinfo($file, PATHINFO_EXTENSION);

    $filename = pathinfo($file, PATHINFO_FILENAME);

    if (file_exists($file) && is_readable($file)) 
    {
            if(!in_array($ext, $site['ext']))
            {
                return false;
            }
        
        $content = file_get_contents($file);

        $frontMatter = [];
        if (preg_match('/^---\s*\n(.*?\n)---\s*\n/sm', $content, $matches)) {
            $frontMatter = Yaml::parse($matches[1]);
            $content = substr($content, strlen($matches[0]));
        }

        $page = $frontMatter;

        if(!isset($page['title']))
        {
            if (preg_match('/^# (.+?)$/m', $content, $matches)) {
                $page['title'] = $matches[1];
                $content = substr($content, strlen($matches[0]));
            }
            else
            {
                $page['title'] = $site['default-title'];
            }
            
        }

        $page['title']= trim($page['title']);

        /* Extract tags */

        preg_match_all('/(?<!\\\\)\s#\w+/', $content, $tagmatches);
    
        // Get tags from the markdown
        $tags = array_map(function($tag) {
            $tag = trim($tag);
            $tag = ltrim($tag, '#');
            return $tag;
        }, $tagmatches[0]);
    
        if(!isset($page['tags']))
        {
            $page['tags'] = [];
        }
        else if (!is_array($page['tags'])) {
            $page['tags'] = (array)$page['tags'];
        }
        
        $page['tags'] = array_merge($page['tags'], $tags);
        $page['tags'] = array_map('strtolower', $page['tags']);
        $page['tags'] = array_unique($page['tags']);

        //Remove line containing only tags
        $content = preg_replace('/^(?:\s*#\w+\s*?)*$/m', '', $content);
        // Use regular expression to find and modify Markdown links
        $pattern = '/\[(.*?)\]\((.*?)\)/';
        $content = preg_replace_callback('/\[(.*?)\]\((.*?)\)/', fn($matches) => '[' . $matches[1] . '](' . rtrim($matches[2], '/') . '/)', $content);
        $content = preg_replace_callback($pattern, 'add_base_to_links', $content);
        $content = $parsedown->text($content);
        $page['content']= trim($content, " \n\r\t");

         if(!$site['build-drafts'])
        {   
             /* Only parse if file has front matter */
            if(sizeof($page) == 0) {
            return;}
        }

        $slug = str_replace($site_dir,"", $file);
        if($filename=="index")
        {       
            $slug = str_replace($filename.".".$ext, "", $slug);   
        }
        else
        {
            $slug = str_replace(".".$ext, "", $slug);
        }

        $page['slug'] = format_slug($slug);
        /*Adding a trailing slash to be consistent in URL scheme */
        
       
        $page['file_path'] = $file;

        $fileContent = "";

        if(isset($page['layout']))
        {   
            $file = join_path($tunnel_dir, 'layout', $page['layout'].".php");
            if (file_exists($file) && is_readable($file)) 
            {
                $page['layout'] = $page['layout'];
            }
            else 
            {
                $page['layout'] = $site['default-layout'];
            }
             
        }
        else
        {   

            $page['layout'] = $site['default-layout'];
        }



        return $page;
    }

    return false;
}

/* Create HTML for a parsed page content */
function createHTMLFile($page)
{   
    global $site, $tunnel_dir, $site_dir, $db;

    if(!$page)
    {
        return false;
    }

    if(in_array('draft', $page['tags']) && !$site['build-drafts'])
    {
        return;
    }

    $destination = join_path($tunnel_dir, 'site', $page['slug']);

    if (!is_dir($destination)) 
    {   
        mkdir($destination, 0700, true); // true for recursive create
    }

    $destination = join_path($destination, "index.html");

    ob_start();
    include(join_path($tunnel_dir, 'layout', $page['layout'].".php"));
    $fileContent = ob_get_clean();
    $file = fopen($destination, "w");
    fwrite($file, $fileContent);
    fclose($file);

    $db_slug = $page['slug'];
    if($db_slug=="")
    {
        $db_slug = "index";
    }


    $fileMtime = filemtime($page['file_path']);

        $stmt = $db->prepare('INSERT OR REPLACE INTO cache_table (slug, mtime, btime) VALUES (:slug, :mtime, :btime)');
        $stmt->bindValue(':slug', $db_slug, SQLITE3_TEXT);
        $stmt->bindValue(':mtime', $fileMtime, SQLITE3_TEXT);
        $stmt->bindValue(':btime', time(), SQLITE3_TEXT);
    $stmt->execute();


}

/*Scan directory and gives the list of paths are supported for parsing */
function scan($dir)
{  
    global $site, $tunnel_dir, $site_dir;
    $pages = [];
    $entries = scandir($dir);
    foreach ($entries as $entry) {
        if ($entry !== '.' && $entry !== '..' && !str_starts_with($entry, "_")) 
        {   
            $path = $dir.DS.$entry;
            if (is_file($path)) {
                $page = parse($path);
                if($page)
                {            
                    array_push($pages, $page);
                }
            } elseif (is_dir($path)) {

                if(!str_contains($path, $tunnel_dir))
                { 
                    $pages = array_merge($pages, scan($path));
                }
           }
        }
    } 
    return $pages;
}


/* Get all parsed content of all the pages */
function get_all_pages()
{
    global $site_dir;
    return sortByDate(scan($site_dir));
}

/* Generate HTML Files for all the pages given */
function generateHTMLFiles($pages)
{
    for($i=0; $i < sizeof($pages); $i++) {
        createHTMLFile($pages[$i]);
    }
}

/* Sort pages by date */
function sortByDate($pages)
{
     usort($pages, function($a, $b){
        if(!isset($a['date']))
        {
            $a['date'] = -1;
        }

        if(!isset($b['date']))
        {
            $b['date'] = -1;
        }

        return $b['date'] - $a['date'];
    });

     return $pages;
}

   
function generateFeed()
{
    global $site, $tunnel_dir, $site_dir;

    $file = join_path($tunnel_dir, "layout", "feed".".php");
    
    if (file_exists($file) && is_readable($file)) 
    {
        include($file);
    }
}

function get_file_path($slug)
{   $slug = format_slug($slug);
    global $site, $site_dir, $tunnel_dir;

    $slug = trim($slug, DS);
    $slug = trim($slug, "/");
    foreach($site['ext'] as $ext)
    {
        $file_path = join_path($site_dir, str_replace('/', DS, $slug.".".strtolower($ext)));
        if(file_exists($file_path))
        {   
            if(str_contains($file_path, $tunnel_dir)) {return false;}
            return $file_path;
        }
        else
        {
            $file_path = join_path($site_dir, str_replace('/', DS, join_path($slug, 'index'.".".strtolower($ext))));
            if(file_exists($file_path))
            {
                if(str_contains($file_path, $tunnel_dir)) { return false;}
                return $file_path;
            }
        }
    }

    return false;
}

function build_required($slug)
{   
    global $db;
    $file_path = get_file_path($slug);
    $slug = format_slug($slug);

    if(isset($_GET['build']))
    {
        return true;
    }

    
    if($file_path)
    {
           $fileMtime = filemtime($file_path);
            $db_slug = $slug;
            if($db_slug=="")
            {
                $db_slug = "index";
            }
            $stmt = $db->prepare('SELECT mtime FROM cache_table WHERE slug = :slug');
            $stmt->bindValue(':slug', $db_slug, SQLITE3_TEXT);
            $result = $stmt->execute();

            if ($result) {
                $row = $result->fetchArray(SQLITE3_ASSOC);
                if ($row) {
                    // Convert the mtime from the database to a timestamp
                    $cacheMtime = $row['mtime'];
                    if ($cacheMtime === false) {
                        return false; // Error converting mtime to timestamp
                    }

                    // Compare the modification times
                    if ($cacheMtime < $fileMtime) {
                        return true; // Build is required
                    }
                }
            }
    }

    return false;
}

function get_page($slug)
{
    global $site, $site_dir, $tunnel_dir;
    $slug = format_slug($slug);
    $file_path = get_file_path($slug);
    $rendered_path = join_path($tunnel_dir,"site", str_replace('/', DS, $slug.DS."index.html"));
    if(build_required($slug))
    {
        createHTMLFile(parse($file_path));
    }
    else 
    {  
        if(!file_exists($rendered_path))
        {
            createHTMLFile(parse($file_path));
        }
        
    }

    if(file_exists($rendered_path))
    {
        echo file_get_contents($rendered_path);
    }
    else if(file_exists(join_path($tunnel_dir,"site", str_replace('/', DS,'404'.DS."index.html"))))
    {
        echo file_get_contents(join_path($tunnel_dir,"site", str_replace('/', DS,'404'.DS."index.html")));
    }
    else 
    {
        echo "File not found";
    }
}

function removeQueryParameters($url) {
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

 function rrmdir($dir) { 
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


$requested_slug =  str_replace($site['base'],'',removeQueryParameters($_SERVER['REQUEST_URI']));

if(isset($_GET['buildsite']))
{   
    rrmdir(join_path($tunnel_dir,'site'));

    $sql = "DELETE FROM cache_table";

    $db->exec($sql);

    generateHTMLFiles(get_all_pages());
    generateFeed();
    if(!file_exists(join_path($tunnel_dir,'site', '.htaccess')))
    {
         $htaccessContent = <<<HTACCESS
            # Deny access to everyone from web
            Require all denied
            HTACCESS;

        file_put_contents(join_path($tunnel_dir,'site','.htaccess'), $htaccessContent);

    }
}

get_page($requested_slug);