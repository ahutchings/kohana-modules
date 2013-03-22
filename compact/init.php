<?php

//ini_set('zlib.output_compression', '0');

Route::set('compact', 'compact/<action>(/<group>)')
  ->defaults(array(
    'directory' => 'compact',
    'controller' => 'index',
    'group' => 'default',
    'action' => 'compact'
  ));