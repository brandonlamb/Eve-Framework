<?php
namespace Eve\Database;

class Pdo extends \PDO
{
	/**
	 * Connection settings
	 *
	 * @var string
	 */
	protected $_type;
	protected $_dsn;
	protected $_username;
	protected $_password;

	/**
	 * Suppress error messages (mainly for sessions)
	 *
	 * @var bool
	 */
	private $_noerrors;

	/**
	 * Constructor
	 *
	 * @param string $type
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param bool $noerrors
	 * @return void
	 */
	public function __construct($dsn, $username, $password, $noerrors)
	{
		// Set error setting
		$this->_dsn			= $dsn;
		$this->_username	= $username;
		$this->_password	= $password;
		$this->_noerrors	= $noerrors;

		// Verify dsn type
		$type = substr($dsn, 0, strpos($dsn, ':'));
		switch ($type) {
			case 'mysql': break;
			case 'mssql': break;
			case 'dblib': break;
			case 'sqlsrv': break;
			default: $type = 'mysql';
		}
		$this->_type = $type;

		$this->_connect();
	}

	/**
	 * Create database connection
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function _connect()
	{
		try {
			parent::__construct($this->_dsn, $this->_username, $this->_password);

			// Added to mysql connections to prevent weird error
			if ($this->_type == 'mysql') { \PDO::setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true); }
		} catch (PDOException $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * Getter/setter for database type
	 * @param string $value
	 * @return string
	 */
	public function type($value = null)
	{
		if (null === $value) { return $this->_type; }
		$this->_type = $value;
		return $this;
	}

	public function fetchAll($query, $parameters = array())
	{
		$stmt = $this->_prepareExecute($query, $parameters);

		$rows = $stmt->fetchAll(\PDO::FETCH_CLASS);
		$stmt->closeCursor();

		unset($stmt);
		return $rows;
	}

	public function fetchOne($query, $parameters = array())
	{
		$stmt = $this->_prepareExecute($query, $parameters);

		$row = $stmt->fetchObject();
		if (!is_object($row)) { $row = false; }

		$stmt->closeCursor();
		unset($stmt);
		return($row);
	}

	public function fetchColumn($query, $parameters = array(), $column = 0)
	{
		$column = abs((int) $column);

		$stmt = $this->_prepareExecute($query, $parameters);
		$fetchedColumn = $stmt->fetchColumn($column);

		$stmt->closeCursor();
		unset($stmt);
		return($fetchedColumn);
	}

	public function modify($query, $parameters)
	{
		$stmt = $this->_prepareExecute($query, $parameters);
		return($stmt->rowCount());
	}

	protected function _prepareExecute($query, $parameters = array())
	{
		$stmt = $this->prepare($query);
		$stmt->execute($parameters);
		return $stmt;
	}
}
