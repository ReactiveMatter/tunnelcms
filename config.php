<?php
# The title of the website
$site['title'] = 'Tunnel CMS';

# Extensions that should be served by the CMS
$site['ext'] = ['md', 'txt'];

# Default layout for the posts. It is to be ensure that a php file with this file exists in template folder.
$site['default_layout'] = 'default';

# Default format for display of date
$site['date_format'] = 'd F, Y';

# List of enabled plugins.
# $site['plugins'][] = 'drafts';
# $site['plugins'][] = 'feed';
$site['plugins'][] = 'cache_cleanup';