<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Redis connection.
 *
 * @package    Kohana/Redis
 * @category   Drivers
 * @author     Xackery
 * @license    http://kohanaphp.com/license
 */
class Kohana_Redis_Default extends RedisLibrary {

	// Database in use by each connection
	protected static $_current_databases = array();

	// Use SET NAMES to set the character set
	protected static $_set_names;

	// Identifier for this connection within the PHP driver
	protected $_connection_id;

	// MySQL uses a backtick for identifiers
	protected $_identifier = '`';

	public function connect()
	{
		if ($this->_connection)
			return;

		// Extract the connection parameters, adding required variabels
		extract($this->_config['connection'] + array(
			'hostname'   => '',
			'port'		 => '',
			'password'   => '',
			'timeout'	 => 2.5,
			'lifetime'	 => 1800,
			'persistent' => FALSE,
		));

		// Prevent this information from showing up in traces
		unset($this->_config['connection']['password']);
		$this->_connection = new Redis();

		try {
			
			if ($persistent) {

				// Create a persistent connection
				$this->_connection->pconnect($hostname, $port, $timeout);
			} else {

				// Create a connection and force it to be a new link
				$this->_connection->connect($hostname, $port, $timeout);
			}
		}
		catch (Exception $e)
		{
			// No connection exists
			$this->_connection = NULL;

			throw new Redis_Exception(':error',
				array(':error' => $e->getMessage()),
				$e->getCode());
		}

		// \xFF is a better delimiter, but the PHP driver uses underscore
		$this->_connection_id = sha1($hostname.'_'.$password);

		if ( ! empty($this->_config['connection']['variables']))
		{
			// Set session variables
			$variables = array();

			foreach ($this->_config['connection']['variables'] as $var => $val)
			{
				$variables[] = 'SESSION '.$var.' = '.$this->quote($val);
			}

			//mysql_query('SET '.implode(', ', $variables), $this->_connection);
		}
	}



	public function disconnect()
	{
		try
		{
			// Database is assumed disconnected
			$status = TRUE;

			if (is_resource($this->_connection))
			{
				if ($status = mysql_close($this->_connection))
				{
					// Clear the connection
					$this->_connection = NULL;

					// Clear the instance
					parent::disconnect();
				}
			}
		}
		catch (Exception $e)
		{
			// Database is probably not disconnected
			$status = ! is_resource($this->_connection);
		}

		return $status;
	}

	public function get($key) {

		$this->_connection or $this->connect();

		return $this->_connection->get($key);
	}

	public function set($key, $value, array $params) {

		$this->_connection or $this->connect();
		
		$this->_connection->set($key, $value, $params);

	}

	public function del($key) {

		$this->_connection or $this->connect();
		
		$this->_connection->del($key);

	}
	
	public function expire($key, $seconds) {
		
		$this->_connection or $this->connect();
		$this->_connection->setTimeout($key, $seconds);
	}

} // End Redis_Default
