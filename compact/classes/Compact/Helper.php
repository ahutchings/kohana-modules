<?php
/**
 * Class Sminify_Helper 
 * @package Sminify
 */

/**
 * Helper functions for Sminify module
 * 
 * 
 * @package Sminify
 * @author Erwan Dupeux-Maire <erwan@upyupy.fr>
 * @url http://www.bwat.fr
 * @url http://www.upyupy.fr
 */
class Compact_Helper {
    
    /**
     * Check if URL is valid
     * Return true if the URL is valid.
     * <code>
     * if (Compact_Helper::isValidURL('http://www.site.com/site.js'))
     * {
     * 	// Do action here...
     * }
     * </code>
     *
     * @param string $url
     * @return boolean
     */
    public static function isValidURL($url='')
    {
		return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }
    
    /**
	* removeMultipleSpaces
	* 
	* Replace multiple spaces by a single one
	
	* @param  string $str  	String to clean
	* @return string
	*/
	public static function removeMultipleSpaces($text) 
	{
		// Line breaks
		$text = preg_replace("/[\r\n]+/", "\n", $text);
		// Tabs:
		$text = preg_replace("/[\t]+/", " ", $text);
		// Spaces:
		return preg_replace("/ +/", " ", $text);
	}
	
	/**
	* fixCssUrl
	* 
	* Replace relative image URL by absolute URL in CSS code
	* Url starting by http(s) or by slash will be ignored as they already are absolute.
	* Based on great answer by Mathematical.Coffee to great question of Mog
	* http://stackoverflow.com/questions/9798378/preg-replace-regex-to-match-relative-url-paths-in-css-files
	* 
	* @param  string $css  	CSS code to process
	* @param  string $str  	String to clean
	* @return string
	*/
	public static function fixCssUrl($css, $file) 
	{
		$path = dirname($file).'/';
		
		if (!Compact_Helper::isValidURL($path) && strpos($path, '/')!==0)
		{
			// if path is not an URL and does not start by slash, we need to had a slash at the beginnign
			$path = '/'.$path;
		}
		$search = '#url\((?!\s*[\'"]?(?:https?:)?//?/)\s*([\'"])?#';
		$replace = "url($1{$path}";
		return preg_replace($search, $replace, $css);
	}
}

