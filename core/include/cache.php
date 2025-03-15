<?php

function cache_valid($request)
{   
    global $site;

    if(!file_exists(join_path($site['dir'],'cache',$request['hashes']['slug'].".htm")) || !file_exists(join_path($site['dir'],'cache',$request['hashes']['slug'].".meta.json")))
    {
        return false;
    }

    $meta = json_decode(file_get_contents(join_path($site['dir'],'cache',$request['hashes']['slug'].".meta.json")), true);

    if ($meta === null) {
        return false; // Failed to decode JSON, cache is invalid
    }

    # Change in template files always should invalid cache
    if ($meta['hashes']['template'] && $meta['hashes']['template']!=$request['hashes']['template'])
    {
        return false;
    }

    # No change in content, hence validate cache
    if ($meta['hashes']['content'] && $meta['hashes']['content']==$request['hashes']['content'])
    {
        return true;
    } 

    # Change in file mtime, hence build required
    if($meta['mtime'] && filemtime($request['filepath']) != $meta['mtime'])
    {
        return false;
    }
    
    
    return false;

}

# Return cache content for a given hash of file
function serve_cache($request)
{   global $site;
    if(file_exists(join_path($site['dir'],'cache',$request['hashes']['slug'].".htm")))
    {
        echo file_get_contents(join_path($site['dir'],'cache',$request['hashes']['slug'].".htm")) ;
    }
    else
    {
        exit("Cache error");
    }
}


function create_cache(&$page)
{
    global $request;

    file_put_contents('cache'.DS.$request['hashes']['slug'].".htm", $page['html']);

    $meta['mtime'] = filemtime($request['filepath']);
    $meta['slug'] = $page['slug'];
    $meta['hashes'] = $request['hashes'];

    file_put_contents('cache'.DS.$request['hashes']['slug'].".meta.json", json_encode($meta));
}
