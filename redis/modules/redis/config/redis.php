<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	'default' => array
	(
		'type'       => 'Default',
		'connection' => array(
			'hostname'   => '127.0.0.1',
			'port'		 => 6379,
			'timeout'    => 2.5,
			'password'   => FALSE,
			'persistent' => FALSE,
		),
		'charset'      => 'utf8',
		'caching'      => FALSE,
	)
);
