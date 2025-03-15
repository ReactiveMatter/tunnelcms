<?php

use Symfony\Component\Yaml\Yaml;

function build($request)
{
    global $site;
    $file = $request['filepath'];
    $filename = pathinfo($file, PATHINFO_FILENAME);
    $ext = pathinfo($file, PATHINFO_EXTENSION);


    # Initialize Parsedown
    $parsedown = new Parsedown();

    /* Setting default values if not provided in config */
    if(!isset($site['ext'])){$site['ext'] = ['md'];}
    if(!isset($site['date_format'])){$site['date_format'] = "Y-m-d";}
    if(!isset($site['default_layout'])){$site['default_layout'] = "default";}

    # Read file contents
    $content = file_get_contents($file);

    $frontMatter = [];
    if (preg_match('/^---\s*\n(.*?\n)---\s*\n/sm', $content, $matches)) {
        $frontMatter = Yaml::parse($matches[1]);
        $content = substr($content, strlen($matches[0]));
    }

    $page = $frontMatter;

    # Setting page title
    if(!isset($page['title']))
    {
        # Make first heading as page title
        if (preg_match('/^# (.+?)$/m', $content, $matches)) {
            $page['title'] = $matches[1];
            $content = substr($content, strlen($matches[0]));
        }
        else
        {   
            # Else use filename as page title
            $page['title'] = ucwords(pathinfo($file, PATHINFO_FILENAME));
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
    $content = preg_replace_callback($pattern, 'add_base_to_links', $content);
    $content = $parsedown->text($content);
    
    $page['content']= trim($content, " \n\r\t");

    # Updating slug to ensure index files in sub directory and named files have same slug 
    $slug = str_replace($site['dir'].DS.'content',"", $file);

    if($filename=="index")
    {       
        $slug = str_replace($filename.".".$ext, "", $slug);   
    }
    else
    {
        $slug = str_replace(".".$ext, "", $slug);
    }

    $page['slug'] = format_slug($slug);


    if(!isset($page['layout']) && isset($site['default_layout']))
    {

        $page['layout'] = $site['default_layout'];

    }

    return $page;
}


/*Scan directory and gives the list of paths are supported for parsing */
function get_all_pages($dir = null)
{   
    global $site;
    if($dir == null)
    {
        $dir = join_path($site['dir'],'content');
    }
    # When called from template, page type is set to dynamic.
    $pages = [];
    $entries = scandir($dir);
    foreach ($entries as $entry) {
        if ($entry !== '.' && $entry !== '..' && !str_starts_with($entry, "_")) 
        {   
            $path = $dir.DS.$entry;
            if (is_file($path)) {
                $p = parse($path);
                if($p)
                {            
                    array_push($pages, $p);
                }
            } elseif (is_dir($path)) {
                    $pages = array_merge($pages, get_all_pages($path));
           }
        }
    } 
    return sort_pages_by_date($pages);
}

// Parse a file and build it
function parse($file) {

    global $site;
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $filename = pathinfo($file, PATHINFO_FILENAME);
    if (file_exists($file) && is_readable($file)) 
    {
            if(!in_array($ext, $site['ext']))
            {
                return false;
            }

            $r['filepath'] = $file;
            return build($r);
    }
        
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


# Render the parsed parsed page
function render($page)
{
    global $site;

    if(!isset($page['layout']) && isset($site['default_layout']))
    {
        $page['layout'] = $site['default_layout'];
    }
    

    if(isset($page['layout']) && file_exists(join_path($site['dir'],'template', $page['layout'].".php")))
    {   

         include(join_path($site['dir'],'template', $page['layout'].".php")); 
    }
    else if(file_exists(join_path($site['dir'],'template', $site['default_layout'].".php")))
    {   
         include(join_path($site['dir'],'template', $site['default_layout'].".php"));    
    }
    else
    {
        exit("Template error");
    }

}

/* Sort pages by date */
function sort_pages_by_date(&$pages)
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
