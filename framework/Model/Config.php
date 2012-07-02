<?php
namespace Eve\Model;

/**
 * @package Eve\Model
 */
class Config implements \Serializable
{
	protected $defaultConnection;
	protected $connections = array();
	protected static $typeHandlers = array(
		'string' => '\Spot\Type\String',
		'text' => '\Spot\Type\String',

		'int' => '\Spot\Type\Integer',
		'integer' => '\Spot\Type\Integer',

		'float' => '\Spot\Type\Float',
		'double' => '\Spot\Type\Float',
		'decimal' => '\Spot\Type\Float',

		'bool' => '\Spot\Type\Boolean',
		'boolean' => '\Spot\Type\Boolean',

		'datetime' => '\Spot\Type\Datetime',
		'date' => '\Spot\Type\Datetime',
		'timestamp' => '\Spot\Type\Integer',
		'year' => '\Spot\Type\Integer',
		'month' => '\Spot\Type\Integer',
		'day' => '\Spot\Type\Integer',
	);

	/**
	 * Add database connection
	 *
	 * @param string $name Unique name for the connection
	 * @param string $dsn DSN string for this connection
	 * @param array $options Array of key => value options for adapter
	 * @param boolean $defaut Use this connection as the default? The first connection added is automatically set as the default, even if this flag is false.
	 * @return Spot\Adapter\Interface Spot adapter instance
	 * @throws Spot\Exception
	 */
	public function addConnection($name, $dsn, array $options = array(), $default = false)
	{
		// Connection name must be unique
		if (isset($this->connections[$name])) {
			throw new Exception("Connection for '" . $name . "' already exists. Connection name must be unique.");
		}

		$dsnp = \Spot\Adapter\AdapterAbstract::parseDSN($dsn);
		$adapterClass = '\\Spot\\Adapter\\' . ucfirst($dsnp['adapter']);
		$adapter = new $adapterClass($dsn, $options);

		// Set as default connection?
		if (true === $default || null === $this->defaultConnection) {
			$this->defaultConnection = $name;
		}

		// Store connection and return adapter instance
		$this->connections[$name] = $adapter;
		return $adapter;
	}

	/**
	 * Get connection by name
	 *
	 * @param string $name Unique name of the connection to be returned
	 * @return Spot\Adapter\Interface Spot adapter instance
	 * @throws Spot\Exception
	 */
	public function connection($name = null)
	{
		if (null === $name) {
			return $this->defaultConnection();
		}

		// Connection name must be unique
		if (!isset($this->connections[$name])) {
			return false;
		}

		return $this->connections[$name];
	}

	/**
	 * Get default connection
	 *
	 * @return Spot\Adapter\Interface Spot adapter instance
	 * @throws Spot\Exception
	 */
	public function defaultConnection()
	{
		return $this->connections[$this->defaultConnection];
	}

	/**
	 * Get type handler class by type
	 *
	 * @param string $type Field type (i.e. 'string' or 'int', etc.)
	 * @return Spot\Adapter\Interface Spot adapter instance
	 */
	public static function typeHandler($type, $class = null)
	{
		if (null === $class) {
			if (!isset(static::$typeHandlers[$type])) {
				throw new \InvalidArgumentException(
					"Type '$type' not registered. Register the type class handler with " . __METHOD__ . "'$type', '\Namespaced\Path\Class')."
				);
			}
			return static::$typeHandlers[$type];
		}

		if (!class_exists($class)) {
			throw new \InvalidArgumentException(
				"Second parameter must be valid className with full namespace. Check the className and ensure the class is loaded before registering it as a type handler."
			);
		}

		return self::$typeHandlers[$type] = $class;
	}

	/**
	 * Default serialization behavior is to not attempt to serialize stored
	 * adapter connections at all (thanks @TheSavior re: Issue #7)
	 *
	 * @return string
	 */
	public function serialize()
	{
		return serialize(array());
	}

	/**
	 * @return mixed
	 */
	public function unserialize($serialized)
	{
	}
}
