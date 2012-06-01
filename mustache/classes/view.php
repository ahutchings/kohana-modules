<?php defined('SYSPATH') or die('No direct script access.');

class View extends Kohana_View {
	
	static protected $kohanaVariables_ = null;
	
	public function set_filename($file) {
		$mustacheFile = Kohana::find_file('views', $file, 'mustache');
		// If there's no mustache file by that name, do the default:
		if ($mustacheFile === false) return Kohana_View::set_filename($file);
		
		$this->_file = $mustacheFile;

		return $this;
	}
	
	
	protected static function kohanaVariables() {
		if (self::$kohanaVariables_) return self::$kohanaVariables_;
		
		self::$kohanaVariables_ = array(
			'DOCROOT' => DOCROOT,
			'APPPATH' => APPPATH,
			'MODPATH' => MODPATH,
			'SYSPATH' => SYSPATH,
			'baseUrl' => Kohana::$base_url,
		);
		
		return self::$kohanaVariables_;
	}
	
	
	protected static function capture($kohana_view_filename, array $kohana_view_data) {
		$extension = pathinfo($kohana_view_filename, PATHINFO_EXTENSION);
		// If it's not a mustache file, do the default:
		if ($extension != 'mustache') return Kohana_View::capture($kohana_view_filename, $kohana_view_data);
		
		$vars = Arr::merge(self::kohanaVariables(), View::$_global_data);
		$vars = Arr::merge($vars, $kohana_view_data);
		
		$m = new Mustache;
		$fileContent = file_get_contents($kohana_view_filename);
		return $m->render($fileContent, $vars);
	}
	
}
