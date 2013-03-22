<?php defined('SYSPATH') or die('No direct script access.');

return array(
  'cache' => true,
  'cache_lifetime' => 3600,
  'debug' => false,
  'gzip' => false,
  'allowCURL' => 'false', // use of PHP CURL to get files contents from URL. CURL will be use only if the file path looks like a valid URL.
  'groups' => array(
  /*
  'groupName1' => array
		(
			'css' => array
			(
				'/assets/css/template.css',
				'/assets/css/colors.css',
				'http://www.site.com/css/style.css'
			),
			'js' => array
			(
				'/assets/js/jquery.js',
				'/assets/css/site.js',
				'http://www.site.com/js/script.js'
			)
		)
   'groupName2' => array(...)
	*/
   )
);
