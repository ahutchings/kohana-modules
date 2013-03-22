A simple kohana module to group and compact JS and CSS assets

It's a very simple minify module that will allow you to easily group several assets files in one smaller file that can be saved in cache. This module has several purposes :
- limit the number of request on assets files 
- reduce the size of the assets by cleaning tabs, spaces and empty lines
- reduce the size of the assets with gzip (not available now)

Works starting form Kohana 3.2.

### Strength and weaknesses

I hope you will like it because :
- it's simple
- it's fast
- it's 100% kohana
- it allows you to group CSS from multiple path and correct the relative image URL for you.
- it allows you to call existing files throught their path or via their URL (very usefull if some of your assets are generated via a Kohana Route). 

Unfortunately, this module does not :
- Handle the JS files who call other files with relative URL (example : tiny_mce.js)

This module does not compact assets files with advanced librairies like js paker, yui compressor...
We want to keep it fast and simple.

### Install

1 - Extract the "compact" folder to "modules" folder.

2 - Activate the module in application/boostrap.php :

    Kohana::modules(array(
      'compact' => MODPATH.'compact'
    ));

3 - To use the cache fonctionnalities, you need to activate the cache module of kohana in Boostrap

	Kohana::modules(array(
      'compact' => MODPATH.'compact',
      'cache'      => MODPATH.'cache'
    ));

    
Note that to use the allowCURL option, you will need to have the CURL library available on your PHP server.

### Usage
1 - Edit config to define assets groups. Create a config file in /application/config/compact.php following this example :

	<?php defined('SYSPATH') or die('No direct script access.');
	return array(
	  'groups' => array(
		  'homepage' => array
			(
				'css' => array
				(
					'assets/css/template.css',
					'assets/css/colors.css',
					'http://www.site.com/css/style.css'
				),
				'js' => array
				(
					'assets/js/jquery.js',
					'assets/css/site.js',
					'http://www.site.com/js/script.js'
				)
			),
		  'siteadmin' => array
			(
				'css' => array
				(
					'assets/admin/css/style.css',
					'assets/css/colors.css'
				),
				'js' => array
				(
					'assets/js/admin.js',
					'assets/js/admin/admin.plugin.js'
				)
			),
			'anothergroup' => array
			(
				'css' => array
				(
					'assets/css/other.css',
				)
			)
	   )
	);

2 - Call the chosen group(s) in your pages.

The module is configured with a route called "compact" witch use this syntax : "compact/kind/groupname".
Kind can be "js" or "css".
You can call the processed files like this :

	<link href="/compact/css/homepage" rel="stylesheet" media="all">
	<script src="/compact/js/homepage" type="text/javascript"></script>
	<link href="/compact/css/anothergroup" rel="stylesheet" media="screen">

Or with Kohana HTML Helpers :

	<?php echo html::style('/compact/css/homepage') ?>
	<?php echo html::script('/compact/js/homepage') ?>


### Configuration options
The main configuration field is the "groups" settings (read before).
You can also specified the following configurations :

	'cache' => true, // cache the compacted files
	'cache_lifetime' => 3600, // time of cache expiration in seconds. 3600 = 1 hour
	'debug' => false, // if set to true, the name of the folder will be displayed in the processed files and their contents won't be compacted
	'gzip' => false, // gzip the result file => not available yet
	'allowCURL' => 'false', // use of PHP CURL to get files contents from URL. CURL will be use only if the file path looks like a valid URL.


### Need to do
- gzip fonctionnalities (after test browser capabilities)
- test in Kohana 3.3 (should be ok :P)
- make it easy to add your own cleaning function

### Authors
Erwan Dupeux-Maire

www.upyupy.fr

www.bwat.fr

https://github.com/Choufourax/kohana-compact/