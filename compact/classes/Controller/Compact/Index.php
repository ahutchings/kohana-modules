<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Compact_Index extends Controller {
	
	
	public function action_css()
	{
		$this->response->headers('Content-Type', 'text/css');
		$this->action_compact('css');
	}

	public function action_js()
	{
		$this->response->headers('Content-Type', 'text/javascript');
		$this->action_compact('js');
	}
	
	public function action_compact($kind='default')
	{
		// load config
		$config = Kohana::$config->load('compact');
		$group = (string) $this->request->param('group');
		$debug = false;
		if(empty($group)) $group = 'default';
		if (!isset($config['cache_name']))
		{
			$config['cache_name'] = 'scompact';
		}
		if (isset($_GET['nocache']))
		{
			$config['cache'] = false;
		}
		if (isset($_GET['debug']) || isset($config['debug']) && $config['debug'])
		{
			Cache::instance()->delete($config['cache_name'].'::'.$kind.'::' . $group);
			$debug = true;
		}
		if(
			!(bool) $config['cache'] ||
			!$content = Cache::instance()->get($config['cache_name'].'::'.$kind.'::' . $group)
		)
		{
			$files = array();
			if (
				isset($config['groups']) 
				&& isset($config['groups'][$group]) 
				&& isset($config['groups'][$group][$kind])
				&& is_array($config['groups'][$group][$kind])
			)
			{
				$files = $config['groups'][$group][$kind];
			}
			$content = '';
			foreach($files as $file)
			{
				if ($debug)
				{
					$content .= "\n\n/*\n===================================== \n=====================================\n"
					.$file
					." \n===================================== \n=====================================\n*/\n\n";
				}
				$tmp = false;
				// Get by URL
				if (
					isset($config['allowCURL'])
					&& $config['allowCURL']
					&& Compact_Helper::isValidURL($file)
				)
				{
					// Init CURL
					$ch = curl_init();
					// Configuration CURL
					curl_setopt($ch, CURLOPT_URL, $file);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					// Get URL content
					$tmp = curl_exec($ch);
					if (!$tmp)
					{
						// Store an error message
						Message::instance()->error(
							__(
								'compactFileCurlErr:file:msg', 
								array(
									':file'=>$file, 
									':msg'=>curl_error ($ch)
								)
							)
						);
						continue;
					}
				}
				// Get by file
				if (!$tmp)
				{
					$tmp = @file_get_contents($file);
					if (!$tmp)
					{
						Message::instance()->error(__('compactFileMissing:file', array(':file'=>$file)));
					}
				}
				if ($tmp && $kind=='css')
				{
					// fix url path
					$tmp = Compact_Helper::fixCssUrl($tmp, $file);
				}
				$content .= $tmp . "\n";
			}
			
			if(!empty($content) && !$debug)
			{
				// light compact
				$content = Compact_Helper::removeMultipleSpaces($content);
				
			}

			if((bool) $config['cache'])
			{
				Cache::instance()->set(
					$config['cache_name'].'::'.$kind.'::' . $group, 
					$content, 
					(int)$config['cache_lifetime']
				);
			}
		}
		else
		{
			$content = '/* CACHED */'.$content;
		}
		$this->response->body($content);
	}
}