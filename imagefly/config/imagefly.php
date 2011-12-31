<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package   Modules
 * @category  Imagefly
 * @author    Fady Khalife
 * @uses      Image Module
 */

return array
(
    // How long before the browser checks the server for a new version of the modified image (Default: 1 Week)
    'cache_expire' => 7 * 24 * 60 * 60,

    // Path to the image cache directory you would like to use (Default: 'media/imagecache/')
    // Dont forget the trailing slash!
    'cache_dir' => 'media/imagecache/',

    // Mimic the source file filestructure within the cache_dir (Default: TRUE)
    // Useful if you have lots of images and do not want to store them in one level
    'mimic_sourcestructure' => TRUE,
);
