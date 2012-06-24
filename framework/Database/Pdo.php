<?php
namespace Eve\Database;

class Pdo extends \PDO
{
	/**
	 * Connection settings
	 *
	 * @var string
	 */
	protected $type;
	protected $dsn;
	protected $username;
	protected $password;

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
	public function __construct($dsn, $username, $password)
	{
		// Set error setting
		$this->dsn			= $dsn;
		$this->username	= $username;
		$this->password	= $password;

		// Verify dsn type
		$type = substr($dsn, 0, strpos($dsn, ':'));
		switch ($type) {
			case 'mysql':
				break;
			case 'mssql':
				break;
			case 'dblib':
				break;
			case 'sqlsrv':
				break;
			case 'ibm':
				break;
			default:
				$type = 'mysql';
		}
		$this->type = $type;

		$this->connect();
	}

	/**
	 * Create database connection
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function connect()
	{
		try {
			parent::__construct($this->dsn, $this->username, $this->password);

			// Added to mysql connections to prevent weird error
			switch ($this->type) {
				case 'mysql':
					\PDO::setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
					break;
				case 'ibm':
					\PDO::setAttribute(\PDO::ATTR_PERSISTENT, true);
					\PDO::setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
					break;
				default:
			}
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
		if (null === $value) {
			return $this->type;
		}
		$this->type = $value;
		return $this;
	}

	/**
	 * Run prepared statement, return multiple rowsets
	 *
	 * @param string $query
	 * @param array $parameters
	 * @return array
	 */
	public function fetchAll($query, $parameters = array())
	{
		$stmt = $this->query($query, $parameters);

		// Fetch rows as associative array
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		// Close cursor and delete $stmt variable
		$stmt->closeCursor();
		unset($stmt);

		// Return rows array
		return $rows;
	}

	/**
	 * Run prepared statement, return single rowset
	 *
	 * @param string $query
	 * @param array $parameters
	 * @return array
	 */
	public function fetchOne($query, $parameters = array())
	{
		$stmt = $this->query($query, $parameters);

		// Fetch rows as associative array
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!is_array($row)) {
			$row = false;
		}

		// Close cursor and delete $stmt variable
		$stmt->closeCursor();
		unset($stmt);

		// Return rows array
		return $row;
	}

	public function fetchColumn($query, $parameters = array(), $column = 0)
	{
		$column = abs((int) $column);

		$stmt = $this->query($query, $parameters);
		$fetchedColumn = $stmt->fetchColumn($column);

		$stmt->closeCursor();
		unset($stmt);

		return $fetchedColumn;
	}

	public function modify($query, $parameters)
	{
		$stmt = $this->query($query, $parameters);
		return $stmt->rowCount();
	}

	protected function query($query, $parameters = array())
	{
		$stmt = $this->prepare($query);
		$stmt->execute($parameters);
		return $stmt;
	}
}
