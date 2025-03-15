<?php

// Global hook storage
/**
 * This define four hooks
 * start: It is called before the render process is initiated. The $site and $requests parameters are available.
 * before_render: It is called before HTML is being rendered but after $page is build.
 * after_render: It is called after HTML is rendered but before caching.
 * end: It is called after the render process is complete.
 */

$hooks = ['start', 'before_render', 'after_render', 'end'];

/**
 * Register a plugin callback to a hook
 */
function add_hook(string $hook, callable $callback) {
    global $hooks;
    $hooks[$hook][] = $callback;
}

/**
 * Execute all callbacks attached to a hook
 */
function run_hook(string $hook, ...$args) {
    global $hooks;
    if (!isset($hooks[$hook])) return;
    foreach ($hooks[$hook] as $callback) {
        $callback(...$args); // Call the function with arguments
    }
}


/*Load plugins*/

if(isset($site['plugins']))
{   
  $plugin_dir = "core/plugins";
    if (is_dir($plugin_dir)) {
        foreach (scandir($plugin_dir) as $plugin) {
            if ($plugin === "." || $plugin === "..") {
                continue;
            }

            if (is_file(join_path($plugin_dir,$plugin))) {
                $filename = pathinfo(join_path($plugin_dir,$plugin),  PATHINFO_FILENAME);
                if (in_array($filename, $site['plugins'])) {
                    require_once('core/plugins/'.$filename.".php");
                }
            }
        }
    }
}

