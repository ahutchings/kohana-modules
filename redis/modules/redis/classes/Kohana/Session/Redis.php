<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Redis-based session class.
 *
 * @package    Kohana/Redis
 * @category   Session
 * @author     Xackery
 * @license    http://kohanaphp.com/license
 */
class Kohana_Session_Redis extends Session {

	// Redis instance
	protected $_rd;

	// The current session id
	protected $_session_id;

	// The old session id
	protected $_update_id;

	public function __construct(array $config = NULL, $id = NULL) {
		if ( ! isset($config['group'])) {
			// Use the default group
			$config['group'] = RedisLibrary::$default;
		}


		// Load the database
		$this->_rd = RedisLibrary::instance($config['group']);

		parent::__construct($config, $id);

	}

	public function id()
	{
		return $this->_session_id;
	}

	protected function _read($id = NULL)
	{

		if ($id OR $id = Cookie::get($this->_name))
		{
			$result = $this->_rd->get($id);

			if (!empty($result)) {
				// Set the current session id
				$this->_session_id = $this->_update_id = $id;

				// Return the contents
				return $result;
			}
		}

		// Create a new session id
		$this->_regenerate();

		return NULL;
	}

	protected function _regenerate()
	{
		// Create a new session id
		$id = str_replace('.', '-', uniqid(NULL, TRUE));

		return $this->_session_id = $id;
	}

	protected function _write()
	{
		if (!empty($this->_rd->get($this->_session_id))) {
			
			$this->_rd->expire($this->_session_id, $this->_lifetime);
		}
		
		$this->_rd->set($this->_session_id, $this->__toString(), array('nx', 'ex'=> $this->_lifetime));

		// Update the cookie with the new session id
		Cookie::set($this->_name, $this->_session_id, $this->_lifetime);

		return TRUE;
	}

	/**
	 * @return  bool
	 */
	protected function _restart()
	{
		$this->_regenerate();

		return TRUE;
	}

	protected function _destroy()
	{
		if ($this->_update_id === NULL)
		{
			// Session has not been created yet
			return TRUE;
		}
		$this->_rd->del($this->_update_id);

		// Delete the cookie
		Cookie::delete($this->_name);

		return TRUE;
	}

} // End Session_Redis
