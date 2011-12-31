<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package   Modules
 * @category  Imagefly
 * @author    Fady Khalife
 * @uses      Image Module
 */
 
class Controller_ImageFly extends Controller
{
    public function action_index()
    {
        $this->auto_render = FALSE;
        new ImageFly();
    }
}
