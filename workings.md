# Workings
The wokring process of Tunnel CMS is as follows

- Init.php: It boostraps all required files.
	- Loads $config.php which initializes $site (site wide config)
	- Initializes $request parameter
	- Run `start` hook
	- If cache exists and valid, serve cache.
		- Check chache: whether cache file and metadata for cache exists.
		- Check content and template dir hash, compare present hash and the one in the cache metadata. If any of it has changed, cache is invalid
		- Else serve cache. And update cache_meta if required.
	- Process ends here, else continue
	- Build process is initiated
- Build.php:
	- Scan the content directory
	- Loads all plugins and registers hook
	- Parses the requested file
		- If list of all page is required
			- Parses all files and initializes $pages variable
		- YAML and Markdown content is processed and $page variable is initialized
		- `before_render` hook is called. Here the parse Markdown to HTML content is avialable in `$page['content']`;
		- Template page is called to create HTML output.
		- The cache is saved
		- `after_render` hook is called
- `end` hook is called
- The HTML output is served