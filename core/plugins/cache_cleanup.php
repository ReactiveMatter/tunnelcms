<?php
/* Cache clean up plugin */

add_hook('start','remove_old_cache');

# Recursively remove directory
function remove_old_cache() {
   global $site;
   $cache_dir = join_path($site['dir'], 'cache');

   if (is_dir($cache_dir)) { 

     $objects = scandir($cache_dir);
     foreach ($objects as $object) { 
	  if ($object === "." || $object === "..") {continue;}

     $file_path = join_path($cache_dir, $object);
       if (is_file($file_path))
       	{ 	
       		if(time() - filemtime($file_path) > 30*24*60*60)
   				{unlink($file_path);}
       	} 
     }
   }



 }