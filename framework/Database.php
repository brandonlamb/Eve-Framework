<?php
/**
 * Eve Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package Eve
 * @version 0.1.0
 */
namespace Eve;

// Namespace aliases
use Eve\Database;
use Eve\Mvc;

class Database extends Mvc\Component
{
	/**
	 * An array of open connections
	 *
	 * @var array
	 */
	protected $_connections = array();

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_DRIVER	= 'driver';
	const CONF_DSN		= 'dsn';
	const CONF_USER		= 'username';
	const CONF_PASS		= 'password';
	const CONF_NOERR	= 'noerrors';

	/**
	 * Magic getter for connetions
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->getConnection($key);
	}

	/**
	 * Magic setter for connetions
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		return $this->setConnection($key, $value);
	}

	/**
	 * Get database connection
	 *
	 * @param string $name
	 * @return Database\Connection
	 */
	public function getConnection($name)
	{
		// If connection exists return it
		if (!isset($this->_connections[$name])) {
			// Check if configuration exists
			if (isset($this->_config['connections'][$name]) && $config = $this->_config['connections'][$name]) {
				$this->createConnection($name, $config);
			} else {
				throw new \RuntimeException('Database \'' . $name . '\' is not defined in the configuration.');
			}
		}
		return $this->_connections[$name];
	}

	/**
	 * Set new database connection
	 *
	 * @param string $name
	 * @param Database\Connection $object
	 * @return Database
	 */
	public function setConnection($name, $object)
	{
		$this->_connections[$name] = $object;
		return $this;
	}

	/**
	 * Return array of connections
	 *
	 * @return array
	 */
	public function getConnections()
	{
		return $this->_connections;
	}

	/**
	 * Create a database connection and assign to connections using $name
	 *
	 * @param string $name, connection name
	 * @param array $config, connection settings
	 * @return Database
	 */
	public function createConnection($name, $config)
	{
		if (!isset(
			$config[static::CONF_DRIVER],
			$config[static::CONF_DSN],
			$config[static::CONF_USER],
			$config[static::CONF_PASS]
		)) {
			throw new \RuntimeException('Configuration for database \'' . $name . '\' is invalid.');
		}

		// Instantiate new connection
		$class = __NAMESPACE__ . '\\Database\\' . $config[static::CONF_DRIVER];
		$this->_connections[$name] = new $class(
			$config[static::CONF_DSN],
			$config[static::CONF_USER],
			$config[static::CONF_PASS],
			isset($config[static::CONF_NOERR]) ? $config[static::CONF_NOERR] : null
		);

		return $this;
	}
}
