<?php defined('SYSPATH') OR die('Restricted access.');
/**
 * Twootstrap; Bootstrap module for Kohana 3.
 *
 * Twootstrap is a Kohana module made for Kohana 3.2 and it
 * wraps up Bootstrap functionalities into Kohana thus making
 * it easier to manage Bootstrap assets and functions.
 *
 * @package	Twootstrap
 * @author	Neo Ighodaro
 */
class Twootstrap_Twootstrap_Core {

	/**
	 * @var array singletons of instantiated twootstrap components
	 */
	protected static $instance = array();


	/**
	 * Creates a singleton of a Twootstrap component.
	 *
	 * Example of use:
	 *
	 *	// Creates a singleton instance of the asset manager
	 *	$asset_manager = Twootstrap::instance('asset');
	 *
	 * @param string $component
	 * @return \component_class
	 */
	public static function instance($component)
	{
		// Component class name, also unique id for instance
		$component_class = 'Twootstrap_'.ucfirst($component);

		if ( ! isset(Twootstrap_Twootstrap_Core::$instance[$component_class]))
		{
			try
			{
				// Load component class and store singleton
				Twootstrap_Twootstrap_Core::$instance[$component_class] = new $component_class;
			}
			catch (Twootstrap_Exception $e)
			{
				// Unable to load component class
				throw new Twootstrap_Exception('Could not locate Twootstrap component: :component', array(
					':component' => $component,
				));
			}
		}

		return Twootstrap_Twootstrap_Core::$instance[$component_class];
	}


	/**
	 * Creates a new instance of a Twootstrap component class.
	 *
	 *	// Creates an instance of the asset manager
	 *	$asset_manager = Twootstrap::factory('asset');
	 *
	 * @param string $component
	 * @return \component_class
	 */
	public static function factory($component)
	{
		// Component class name, also unique id for instance
		$component_class = 'Twootstrap_'.ucfirst($component);

		try
		{
			// Instantiate component
			return new $component_class;
		}
		catch (Twootstrap_Exception $e)
		{
			// Unable to load component class
			throw new Twootstrap_Exception('Could not locate Twootstrap component: :component', array(
				':component' => $component,
			));
		}
	}

} // End Twootstrap_Twootstrap_Core