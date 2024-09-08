# Tunnel CMS

Tunnel CMS is the simplest markdown CMS. It parses markdown files and renders html in real time whenever necessary. However, not every request requires parsing, as Tunnel CMS builds the HTML files and store in cache. Until, the underlying content of the markdown files is changed, the cached HTML is served.

## 🛠️ Installation

Download the `tunnel` folder along with the `.htaccess` file from the [Github directory](https://github.com/ReactiveMatter/tunnelcms) `https://github.com/ReactiveMatter/tunnelcms`.

Place them in the root of your website, and run `composer install` in tunnel directory.

If you don't want to use composer, download the [latest release](https://github.com/ReactiveMatter/tunnelcms/releases/tag/public) and place contents in the root of your website.

Tunnel CMS will render your markdown files dynamically, so that you can focus on writing.

## 📝 YAML frontmatter

Tunnel CMS parses YAML front matter from the markdown files and serializes them in the `$page` variable. This can be used by the layout to display content.

Some useful page properties:

| Property | Description |
| --- | --- |
| `layout` | Sets the layout for the file which will be used for rendering HTML. |
| `tags` | Tags for the file |


## 🖥️ Layout

The layout files from `tunnel/layout` folder are used to render html content. The layout can be set in YAML front matter in `layout` property. `default.php` is used when no layout is set.

The `title` property can be set for the page title. If not set, the first heading in the markdown file is set as title.

If a layout requires the list of all parsed files, the function `get_all_pages()` can be called to get and array of pages. Each element will have properties (YAML front matter) set for that page.

## 🏷️ Tags

Files can be assigned tags using the front matter property `tags`. In addition to this, Tunnel CMS assigns tags if hash tags are detected in the file (e.g. `#tag1`).

A line in file which contains only hash tags is removed while rendering.

## Content

Content can be written in Markdown. As Tunnel CMS is based on [Parsedown](https://parsedown.org/), formatting supperted by Parsedown can be used.

For making markdown links relative to the site directory, prepend `$` for the relative link. E.g. `[blog]($/blog)`. The site directory is automatically prepended before the link.

## 💾 Cache

Tunnel CMS stores the rendered HTML files in `tunnel/site` folder with the directory structure same as the root folder. Whenever a file is requested for the first time, a build process in initiated. In the build process, front matter is processed, the HTML content is generated from markdown and saved as HTML files in `tunnel/site`. The file modified time (mtime) and the build time (btime) is entered into an SQLite database (`tunnel/build.db`). If the file modified time does not change, the files from `tunnel/site` are served on request. As and when the file modified time changes, the build process is again initiated.

The build process can be forced by supplying the `?build` parameter in the request. No, value is required for this parameter. To build the entire site, `?buildsite` parameter may be supplied.

It is suggested to build the entire site whenever any major changes are made to the content. Further, the building of site would be required if template files are changed.

💡Tip: The cached files can also be used as static site.

## ⚙️ Config

Config variables are stored in `tunnel/config.php`. They are site wide variables used for the build process, and can also be used by the templates.

| Config variable | Description |
| --- | --- |
| `$site['title']` | Site title |
| `$site['ext']` | The file extensions which will be parsed |
| `$site['default']['layout']` | The default layout for parsing. If set to `page`, `tunnel/layout/page.php` will be used.|
| `$site['default']['title']` | Default title. Will be used if not title is available in the file. |
| `$site['build-drafts']` | Whether to build files tagged as `draft` |
| `$site['date-format']` | The PHP date display format. To be used in layouts.|

Custom variable can be defined in config file, which can be used in the layout files.

## 📜 Feed

If the a feed generating file exists at `tunnel/layout/feed.php`, it will be called during the `buildsite` process. The code for generating feeds can be included in this file.

## License

This project is licensed under the MIT License.
