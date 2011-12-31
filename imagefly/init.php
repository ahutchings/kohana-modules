<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package   Modules
 * @category  Imagefly
 * @author    Fady Khalife
 * @uses      Image Module
 */
 
Route::set('imagefly', 'imagefly/<params>/<imagepath>', array('imagepath' => '.*'))
    ->defaults(array(
        'controller' => 'imagefly',
    ));
    