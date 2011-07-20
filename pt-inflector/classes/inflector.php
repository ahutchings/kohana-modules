<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Inflector helper class for portuguese language based on original kohana english version
 * and Gabriel Gilini cakephp's version.
 *
 * @see https://github.com/gabrielgilini
 * @package    pt-inflector
 * @category   Helpers
 * @author     Fernando Carlétti
 * @copyright  (c) 2007-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Inflector extends Kohana_Inflector {

	/**
	 * Makes a plural word singular.
	 *
	 *     echo Inflector::singular('gatos'); // "gato"
	 *     echo Inflector::singular('appendix'); // "appendix", uncountable
	 *
	 * You can also provide the count to make inflection more intelligent.
	 * In this case, it will only return the singular value if the count is
	 * greater than one and not zero.
	 *
	 *     echo Inflector::singular('gatos', 2); // "gatos"
	 *
	 * [!!] Special inflections are defined in `config/inflector.php`.
	 *
	 * @param   string   word to singularize
	 * @param   integer  count of thing
	 * @return  string
	 * @uses    Inflector::uncountable
	 */
	public static function singular($str, $count = NULL)
	{
		// $count should always be a float
		$count = ($count === NULL) ? 1.0 : (float) $count;

		// Do nothing when $count is not 1
		if ($count != 1)
			return $str;

		// Remove garbage
		$str = strtolower(trim($str));

		// Cache key name
		$key = 'singular_'.$str.$count;

		if (isset(Inflector::$cache[$key]))
			return Inflector::$cache[$key];

		if (Inflector::uncountable($str))
			return Inflector::$cache[$key] = $str;

		if (empty(Inflector::$irregular))
		{
			// Cache irregular words
			Inflector::$irregular = Kohana::config('inflector')->irregular;
		}

		if ($irregular = array_search($str, Inflector::$irregular))
		{
			$str = $irregular;
		}
		else
		{
			$rules = array(
				'/^(.*)(oes|aes|aos)$/i' => '\1ao',
				'/^(.*)(a|e|o|u)is$/i' => '\1\2l',
				'/^(.*)e?is$/i' => '\1il',
				'/^(.*)(r|s|z)es$/i' => '\1\2',
				'/^(.*)ns$/i' => '\1m',
				'/^(.*)s$/i' => '\1',
			);

			foreach($rules as $plural => $singular)
			{
				if(preg_match($plural, $str))
				{
					$str = preg_replace($plural, $singular, $str);
					break;
				}
			}	
		}

		return Inflector::$cache[$key] = $str;
	}

	/**
	 * Makes a singular word plural.
	 *
	 *     echo Inflector::plural('appendix'); // "appendix", uncountable
	 *     echo Inflector::plural('gato');  // "gatos"
	 *
	 * You can also provide the count to make inflection more intelligent.
	 * In this case, it will only return the plural value if the count is
	 * not one.
	 *
	 *     echo Inflector::singular('gato', 3); // "gatos"
	 *
	 * [!!] Special inflections are defined in `config/inflector.php`.
	 *
	 * @param   string   word to pluralize
	 * @param   integer  count of thing
	 * @return  string
	 * @uses    Inflector::uncountable
	 */
	public static function plural($str, $count = NULL)
	{
		// $count should always be a float
		$count = ($count === NULL) ? 0.0 : (float) $count;

		// Do nothing with singular
		if ($count == 1)
			return $str;

		// Remove garbage
		$str = trim($str);

		// Cache key name
		$key = 'plural_'.$str.$count;

		// Check uppercase
		$is_uppercase = ctype_upper($str);

		if (isset(Inflector::$cache[$key]))
			return Inflector::$cache[$key];

		if (Inflector::uncountable($str))
			return Inflector::$cache[$key] = $str;

		if (empty(Inflector::$irregular))
		{
			// Cache irregular words
			Inflector::$irregular = Kohana::config('inflector')->irregular;
		}

		if (isset(Inflector::$irregular[$str]))
		{
			$str = Inflector::$irregular[$str];
		}
		else
		{
			$rules = array(
				'/(.*)ao$/' => '$1oes',
				'/(.*)x/' => '$1x',
				'/(.*)(r|s|z)$/' => '$1$2es',
				'/(.*)(a|e|o|u)l$/' => '$1$2is',
				'/(.*)il$/' => '$1is',
				'/(.*)(m|n)$/' => '$1ns',
				'/([a-z])$/' => '$1s'
			);

			foreach($rules as $singular => $plural)
			{
				if(preg_match($singular, $str))
				{
					$str = preg_replace($singular, $plural, $str);
					break;
				}
			}	
		}

		// Convert to uppsecase if nessasary
		if ($is_uppercase)
		{
			$str = strtoupper($str);
		}

		// Set the cache and return
		return Inflector::$cache[$key] = $str;
	}

} // End Inflector
