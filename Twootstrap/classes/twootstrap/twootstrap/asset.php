<?php defined('SYSPATH') OR die('Restricted access.');
/**
 * Twootstrap asset manager.
 *
 * Manages Twootstrap assets. Allows you to queue assets, unqueue assets, and
 * call queued assets.
 *
 * @package	Twootstrap
 * @subpackage	Twootstrap_Asset
 * @author	Neo Ighodaro
 */
class Twootstrap_Twootstrap_Asset {

	/**
	 * Default asset types supported by Twootstrap.
	 *
	 * @var array
	 */
	protected $_assets = array(
		'styles' => array(),
		'scripts' => array(),
	);

	/**
	 * Queue map. Used by the $this->queue() method to find queues.
	 *
	 * @var array
	 */
	protected $_queue_map = array();

	/**
	 * Throw an error on missing dependency? If FALSE, the asset with the missing
	 * dependency will not be loaded.
	 *
	 * @var boolean
	 */
	protected $_error_missing_dependency = FALSE;


	// Options: dependencies, asset_type, attributes, protocol
	/**
	 * Adds a queue to an asset type. You can state what asset type in the options
	 * array (though this is unnecessary as it automatically detects .css and .js)
	 * file extensions.
	 *
	 * Example of use:
	 *
	 *	// Asset Queue
	 *	Twootstrap::instance('asset')
	 *		->queue('bootstrap', 'assets/css/bootstrap.css')
	 *		->queue('default', 'assets/css/default.css', array('dependencies' => 'bootstrap'))
	 *		->queue('css-again', 'assets/css/css-again.css', array('dependencies' => array('default', 'bootstrap')))
	 *		->queue('other-css', 'assets/other.css', array('protocol' => 'https'))
	 *		->queue('external-css', 'http://domain.ext/style.css')
	 *		->queue('jquery', 'assets/js/jquery.js', array('tag' => 'footer'))
	 *		->queue('bootstrap', 'assets/js/bootstrap.js', array('tag' => 'footer', 'dependencies' => 'jquery'))
	 *		->queue('one-last', 'assets/style.php', array('asset_type' => 'styles'));
	 *
	 * @param type $id
	 * @param type $src
	 * @param array $options
	 * @return \Twootstrap_Twootstrap_Asset
	 * @throws Twootstrap_Exception
	 */
	public function queue($id, $src, array $options = NULL)
	{
		// Force options to be array in case its NULL
		$options = (array) $options;

		// check if we have an asset type specified
		$asset_type = Arr::get($options, 'asset_type', FALSE);

		// No asset type, try to figure out
		if ($asset_type === FALSE)
		{
			// Get the assets 'src' extension
			list($src_ext, $src_filename) = explode('.', strrev($src), 2);

			if (strrev(strtolower($src_ext)) === 'css')
			{
				// This is a stylesheet!
				$asset_type = 'styles';
			}
			elseif (strrev(strtolower($src_ext)) === 'js')
			{
				// This is a script!
				$asset_type = 'scripts';
			}
			else
			{
				// This is not recognized, please specify
				throw new Twootstrap_Exception('Twootstrap could not recognize the asset type of asset: :asset.', array(
					':asset' => $id,
				));
			}
		}

		// Load Dependencies!
		if (isset($options['dependencies']))
		{
			// Get array of dependencies
			$dependencies = (array) $options['dependencies'];

			// Any missing dependency?!
			$missing_dependencies = array();

			foreach ($dependencies AS $dependency)
			{
				if ($this->queued($dependency, $asset_type) === FALSE)
				{
					// Not in the queue map!
					$missing_dependencies[] = $dependency;
				}
			}

			if ($this->_error_missing_dependency AND ! empty($missing_dependencies))
			{
				// Error finding dependency
				throw new Twootstrap_Exception('Twootstrap couldnt find some asset dependencies: :dependencies.', array(
					':dependencies' => implode(', ', $missing_dependencies),
				));
			}
		}

		if ($this->queued($id, $asset_type) === FALSE)
		{
			// Add asset to asset type queue
			$this->_assets[$asset_type][] = array(
				'id' => $id,
				'src' => $src,
				'protocol' => Arr::get($options, 'protocol', FALSE),
				'attributes' => (array) Arr::get($options, 'attributes'),
				'tag' => Arr::get($options, 'tag', 'default'),
			);

			// Last queue_id
			$queue_id = count($this->_assets[$asset_type]) - 1;

			// Add asset to queue map, so $this->queued() can find it
			$this->_queue_map[$asset_type][$id] = $queue_id;
		}
		else
		{
			// Get queue id
			$queue_id = $this->queued($id, $asset_type);

			// Modify the existing queue
			$this->_assets[$asset_type][$queue_id] = array(
				'id' => $id,
				'src' => $src,
				'protocol' => Arr::get($options, 'protocol', NULL),
				'attributes' => (array) Arr::get($options, 'attributes'),
				'tag' => Arr::get($options, 'tag', 'default'),
			);
		}

		// method chaining
		return $this;
	}


	/**
	 * This is just a shortcut method to add secure assets that are within the file system.
	 * External assets do not need to use this method (or even specify a 'protocol' option)
	 * to queue secure assets.
	 *
	 * @param type $id
	 * @param type $src
	 * @param array $options
	 * @return type
	 */
	public function queue_secure($id, $src, array $options = NULL)
	{
		return $this->queue($id, $src, array('protocol' => 'https') + (array) $options);
	}


	/**
	 * Removes an asset group, or an asset ID from a group. If the id specified
	 * is NULL, the asset type group will be reset, else the specified ID from
	 * the asset type group will be removed. This method is chainable.
	 *
	 * Example of use:
	 *
	 *	// Removes the default asset from the styles asset type
	 *	Twootstrap::instance('asset')->unqueue('default', 'styles');
	 *
	 *	// Resets the asset type styles
	 *	Twootstrap::instance('asset')->unqueue(NULL, 'styles');
	 *
	 * @param string|null $id
	 * @param string $asset_type
	 * @return \Twootstrap_Twootstrap_Asset
	 */
	public function unqueue($id = NULL, $asset_type)
	{
		if ($id === NULL)
		{
			// Reset queue in asset type
			$this->_assets[$asset_type] = array();

			// Reset asset type queue map
			$this->_queue_map[$asset_type] = array();
		}
		elseif ($this->queued($id, $asset_type))
		{
			// Remove specific id from asset type queue
			unset($this->_assets[$asset_type][$id]);

			// Remove id from queue map
			unset($this->_queue_map[$asset_type][$id]);
		}

		// Method chaining
		return $this;
	}


	/**
	 * Checks if an asset id has already been queued in a asset type group. Returns
	 * the queue id if it has been queued. Please note that sometimes, the queue map
	 * will mark a valid entry as 0 in the map.
	 *
	 * Example of use:
	 *
	 *	// Do this
	 *	if (Twootstrap::instance('asset')->queued('default', 'styles'))
	 *	{
	 *		// Do something...
	 *	}
	 *
	 *	// ...or this
	 *	if (Twootstrap::instance('asset')->queued('default', 'styles') === FALSE)
	 *	{
	 *		// Do something...
	 *	}
	 *
	 *	// Dont do this, because sometimes queue map has a value of 0, which is false in PHP.
	 *	if ( ! Twootstrap::instance('asset')->queued('default', 'styles'))
	 *	{
	 *		// Do something...
	 *	}
	 *
	 * @param string $id
	 * @param string $asset_type
	 * @return FALSE|int
	 */
	public function queued($id, $asset_type)
	{
		if (isset($this->_queue_map[$asset_type][$id]))
			return $this->_queue_map[$asset_type][$id];

		return FALSE;
	}


	/**
	 * Creates HTML link tags from all the queued stylesheets. Typically for use
	 * in template file.
	 *
	 * Example of use:
	 *
	 *	// Uses the dafault asset tag
	 *	echo Twootstrap::instance('asset')->styles();
	 *
	 *	// Uses a specified asset tag
	 *	$footer_styles = Twootstrap::instance('asset')->styles('footer');
	 *
	 * @param string $asset_tag
	 * @param boolean $print
	 * @return string
	 */
	public function styles($asset_tag = NULL, $print = FALSE)
	{
		// Return the HTML
		if ($print === FALSE)
			return $this->_fetch_assets('styles', $asset_tag);

		// Print the HTML directly
		echo $this->_fetch_assets('styles', $asset_tag);
	}


	/**
	 * Creates HTML link tags from all the queued scripts. Typically for use in
	 * template file.
	 *
	 * Exmaple of use:
	 *
	 *	// Uses default asset tag
	 *	echo Twootstrap::instance('asset')->scripts();
	 *
	 *	// Uses a specified asset tag
	 *	$footer_scripts = Twootstrap::instance('asset')->scripts('footer');
	 *
	 * @param string $asset_tag
	 * @param boolean $print
	 * @return string
	 */
	public function scripts($asset_tag = NULL, $print = FALSE)
	{
		// Return the HTML
		if ($print === FALSE)
			return $this->_fetch_assets('scripts', $asset_tag);

		// Print the HTML directly
		echo $this->_fetch_assets('scripts', $asset_tag);
	}

	/**
	 * Forces the $this->queue() method to throw an exception if dependencies
	 * are not found in the initial queues. This can be used in debug cases.
	 *
	 * Example of use:
	 *
	 *	// This will throw an exception if 'jquery' wasnt queued before
	 *	Twootstrap::instance('asset')
	 *		->require_dependencies()
	 *		->queue('bootstrap-js', 'assets/js/bootstrap.js', array( 'dependencies' => 'jquery' ));
	 *
	 * @return \Twootstrap_Twootstrap_Asset
	 */
	public function require_dependencies()
	{
		// Turn on dependencies error
		$this->_error_missing_dependency = TRUE;

		// method chaining
		return $this;
	}

	/**
	 * This method is used internally to fetch all queued assets, based on the
	 * asset type and the asset type tag.
	 *
	 * @param string $asset_type
	 * @param	string $asset_tag
	 * @return string
	 */
	protected function _fetch_assets($asset_type, $asset_tag = 'default')
	{
		// Fetch the assets
		$assets = (array) Arr::get($this->_assets, $asset_type);

		// The HTML container
		$the_html = '';

		if ( ! empty($assets))
		{
			foreach ($assets AS $id => $asset)
			{
				if ($asset_tag === $asset['tag'])
				{
					var_dump($asset['tag']);
					if ($asset_type === 'styles')
					{
						// Asset HTML result
						$the_html .= HTML::style($asset['src'], $asset['attributes'], $asset['protocol'])."\n";
					}
					elseif ($asset_type === 'scripts')
					{
						// Asset HTML result
						$the_html .= HTML::script($asset['src'], $asset['attributes'], $asset['protocol'])."\n";
					}
				}
			}
		}

		// HTML
		return $the_html;
	}

} // End Twootstrap_Twootstrap_Asset