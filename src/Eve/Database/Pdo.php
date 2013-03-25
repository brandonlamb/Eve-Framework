<?php
namespace Eve\Database;

class Pdo extends \PDO
{
	/**
	 * Constructor
	 *
	 * @param  string $type
	 * @param  string $dsn
	 * @param  string $username
	 * @param  string $password
	 * @param  bool   $noerrors
	 * @return void
	 */
	public function __construct($dsn, $username, $password, array $options = array())
	{
		try {
			parent::__construct($dsn, $username, $password);
			$this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (\PDOException $e) {
			throw new \RuntimeException($e->getMessage());
		}
	}

	/**
	 * Run prepared statement, return multiple rowsets
	 *
	 * @param  string $query
	 * @param  array  $parameters
	 * @return array
	 */
	public function fetchAll($query, array $parameters = array())
	{
		$stmt = $this->prepareExecute($query, $parameters);

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
	 * @param  string     $query
	 * @param  array      $parameters
	 * @return array|bool
	 */
	public function fetchOne($query, array $parameters = array())
	{
		$stmt = $this->prepareExecute($query, $parameters);

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

	/**
	 * Fetch a single result column
	 *
	 * @param  string $query
	 * @param  array  $parameters
	 * @param  int    $column
	 * @return mixed
	 */
	public function fetchColumn($query, array $parameters = array(), $column = 0)
	{
		$column = abs((int) $column);

		$stmt = $this->prepareExecute($query, $parameters);
		$fetchedColumn = $stmt->fetchColumn($column);

		$stmt->closeCursor();
		unset($stmt);

		return $fetchedColumn;
	}

	/**
	 * Execute an INSERT/UPDATE/DELETE statement
	 *
	 * @param  string $query
	 * @param  array  $parameters
	 * @return int
	 */
	public function modify($query, array $parameters = array())
	{
		$stmt = $this->prepareExecute($query, $parameters);

		return $stmt->rowCount();
	}

	/**
	 * Prepare and execute prepared statement
	 *
	 * @param  string         $query
	 * @param  array          $parameters
	 * @return PDOStatement
	 */
	protected function prepareExecute($query, $parameters = array())
	{
		$stmt = $this->prepare($query);
		$stmt->execute($parameters);
		return $stmt;
	}

	/**
	 * Prepare an sql statement
	 * @param string $sql
	 * @param array $options
	 * @return PDOStatement
	 */
	public function prepare($sql, $options = array())
	{
		return parent::prepare($sql, $options);
	}
}
