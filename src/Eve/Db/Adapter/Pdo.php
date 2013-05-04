<?php
namespace Eve\Db\Adapter;

class Pdo extends \PDO
{
	/**
	 * Constructor
	 *
	 * @param string $type
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $options
	 * @return void
	 */
	public function __construct($dsn, $username, $password, array $options = [])
	{
		try {
			parent::__construct($dsn, $username, $password, $options);
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
	public function fetchAll($query, array $parameters = [], $mode = \PDO::FETCH_ASSOC)
	{
		$stmt = $this->prepareExecute($query, $parameters);
		$rows = $stmt->fetchAll($mode);
		$stmt->closeCursor();
		unset($stmt);

		return $rows;
	}

	/**
	 * Run prepared statement, return single rowset
	 *
	 * @param  string     $query
	 * @param  array      $parameters
	 * @return array|bool
	 */
	public function fetchOne($query, array $parameters = [], $mode = \PDO::FETCH_ASSOC)
	{
		$stmt = $this->prepareExecute($query, $parameters);
		$row = $stmt->fetch($mode);
		if (!is_array($row)) {
			$row = false;
		}
		$stmt->closeCursor();
		unset($stmt);

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
	public function fetchColumn($query, array $parameters = [], $column = 0)
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
	public function modify($query, array $parameters = [])
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
	protected function prepareExecute($query, array $parameters = [])
	{
		$stmt = $this->prepare($query);
		$stmt->execute($parameters);
		return $stmt;
	}
}
