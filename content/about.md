---
title: About Tunnel CMS
---

Tunnel CMS is the simplest markdown CMS. It parses markdown files and outputs html in real time whenever necessary. However, not every request requires parsing, as Tunnel CMS builds the HTML files and store in cache. Until, the underlying content of the markdown files is changes, the cached HTML is served.

# 🛠️ Installation

Download the `tunnel` folder along with the `.htaccess` file from the [Github directory](https://github.com/ReactiveMatter/tunnelcms) `https://github.com/ReactiveMatter/tunnelcms`. Place them in the root of your website, and that's it!

Tunnel CMS will render your markdown files dynamically, so that you can focus on writing.

# 📝 YAML frontmatter

Tunnel CMS parses YAML front matter from the markdown files and serializes them in the `$page` variable. This can be used by the layout to display content.

Some useful page properties:

| Property | Description |
| --- | --- |
| `layout` | Sets the layout for the file which will be used for rendering HTML. |
| `category` | Sets the category for the file. One page can have only one category.|
| `tags` | Tags for the file |


# 🖥️ Template and layout

The layout files from `template` folder are used to render html content. The layout can be set in YAML front matter in `layout` property. `default.php` is used when no layout is set.

The `title` property can be set for the page title. If not set, the first heading in the markdown file is set as title.

If a layout requires the list of all parsed files, the function `get_all_pages()` can be called. It returns an array of pages. Each element will have properties set for that page.

# 🏷️ Tags

Files can be assigned tags using the front matter property `tags`. In addition to this, Tunnel CMS assigns tags if hash tags are given in the file (e.g. `#tag1`).

# 💾 Cache

Tunnel CMS stores the rendered HTML files and associated metadata in `cache` folder. Whenever a file is requested for the first time, a build processes in initiated. In the build process, front matter is processed, the HTML content is generated and saved as HTML files in `cache`. 

The file `content` and `template` directory hashes, and file _mtime_ are saved in metadata (a json file).

As and when the file modified time changes, the build process is again initiated.

# ⚙️ Config

Config variable are stored in `config.php`. They are site wide variables used for the build process, and can also be used by the templates.

| Config variable | Description |
| --- | --- |
| `$site['title']` | Site title |
| `$site['ext']` | The file extensions which will be parsed |
| `$site['default_layout']` | The default layout for parsing. If set to `page`, `template/page.php` will be used.|
| `$site['date_format']` | The PHP date display format. To be used in layouts.|

Custom variable can be defined in config file, which can be used in the template files.

# Links

By default markdown links are relative to http root (`/about`). However, to make it relative to Tunnel CMS directory use `$/about`. 

# Workings

The detailed process and control flow is provided in [workings]($/workings).

# License

This project is licensed under the MIT License.

# Credits

Tunnel CMS is developed by ReactiveMatter.

Github repo: https://github.com/ReactiveMatter/

Github blog: https://reactivematter.github.io/