<?php
namespace Eve\Model;

#class Query implements \Countable, \IteratorAggregate
class Query
{
	// Storage for query properties
	public $select	= array();
	public $from	= array();
	public $join	= array();
	public $where	= array();
	public $having	= array();
	public $group	= array();
	public $order	= array();
	public $limit;
	public $offset;

	/**
	 * Returns the static model of the specified AR class.
	 * Query::model()->select('col1')->from('tbl')->where('col1 = ?', $val)->fetchOne();
	 *
	 * @param string $className active record class name.
	 * @return Query
	 */
	public static function factory($className = __CLASS__)
	{
		return new $className();
	}

	/**
	 * Reset object
	 *
	 * @return Query
	 */
	public function reset()
	{
		$this->select	= array();
		$this->from		= array();
		$this->join		= array();
		$this->where	= array();
		$this->having	= array();
		$this->group	= array();
		$this->order	= array();
		$this->limit	= null;
		$this->offset	= null;

		return $this;
	}

	/**
	 * Add to SELECT array
	 * null:	reset the fields array.
	 * string:	"column1" OR "column1", "alias" OR "column1, column2 c2, column3"
	 * array:	array("column1", "column2 c2", "column3")
	 *
	 * @param mixed $columns, string or array of clumns
	 * @param string|null $alias, alias for single column selection
	 * @return Query
	 */
	public function select($columns = '*', $alias = null)
	{
		if (null === $columns) {
			// Reset fields since null was passed
			$this->select = array();
		} elseif (is_string($columns)) {
			$this->select[] = null === $alias ? trim($columns) : trim($columns) . ' AS ' . trim($alias);
		} elseif (is_array($columns)) {
			foreach ($columns as $column) {
				$this->select[] = trim($column);
			}
		}
		return $this;
	}

	/**
	 * Add to FROM tables array
	 * null:	reset the from tables array.
	 * string:	"table1" OR "table1", "alias" OR "table1, table2 t2, table3"
	 * array:	array("table1", "table2 t2", "table3")
	 *
	 * @param mixed $tables, string or array of tables
	 * @param bool $alias, table alias
	 * @return Query
	 */
	public function from($tables, $alias = null)
	{
		if (null === $tables) {
			// Reset fields since null was passed
			$this->from = array();
		} elseif (is_string($tables)) {
			$this->from[] = null === $alias ? trim($tables) : trim($tables) . ' ' . trim($alias);
		} elseif (is_array($tables)) {
			foreach ($tables as $column) {
				$this->from[] = trim($column);
			}
		}
		return $this;
	}

	/**
	 * WHERE conditions
	 *
	 * @param array $conditions Array of conditions for this clause
	 * @param string $type Keyword that will separate each condition - 'AND', 'OR'
	 * @param string $setType Keyword that will separate the whole set of conditions - 'AND', 'OR'
	 */
	public function where(array $conditions = array(), $type = 'AND', $setType = 'AND')
	{
		// Don't add WHERE clause if array is empty (easy way to support dynamic request options that modify current query)
		if ($conditions) {
			$where = array();
			$where['conditions'] = $conditions;
			$where['type'] = $type;
			$where['setType'] = $setType;

			$this->where[] = $where;
		}
		return $this;
	}

	public function whereRaw($condition, $type = 'AND')
	{
		return $this->where(array($condition), $type);
	}

	public function orWhere(array $conditions = array(), $type = 'AND')
	{
		return $this->where($conditions, $type, 'OR');
	}

	public function andWhere(array $conditions = array(), $type = 'AND')
	{
		return $this->where($conditions, $type, 'AND');
	}

	/**
	 * WHERE = condition
	 *
	 * @param array $column
	 * @param mixed $value
	 * @param string $type
	 * @return Query
	 */
	public function whereEqual($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '=', $value), $type);
	}

	/**
	 * WHERE != condition
	 *
	 * @param array $column
	 * @param mixed $value
	 * @param string $type
	 * @return Query
	 */
	public function whereNotEqual($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '!=', $value), $type);
	}

	/**
	 * WHERE LIKE condition
	 *
	 * @param array $column
	 * @param mixed $value
	 * @param string $type
	 * @return Query
	 */
	public function whereLike($column, $value, $type = 'AND')
	{
		return $this->where(array($column, 'LIKE', $value), $type);
	}

	/**
	 * WHERE NOT LIKE condition
	 * @param array $column
	 * @param mixed $value
	 * @param string $type
	 * @return Query
	 */
	public function whereNotLike($column, $value, $type = 'AND')
	{
		return $this->where(array($column, 'NOT LIKE', $value), $type);
	}

	/**
	 * WHERE > condition
	 *
	 * @param array $column
	 * @param string|int $value
	 * @param string $type
	 * @return Query
	 */
	public function whereGt($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '>', $value), $type);
	}

	/**
	 * WHERE >= condition
	 *
	 * @param array $column
	 * @param string|int $value
	 * @param string $type
	 * @return Query
	 */
	public function whereGte($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '>=', $value), $type);
	}

	/**
	 * WHERE < condition
	 *
	 * @param array $column
	 * @param string|int $value
	 * @param string $type
	 * @return Query
	 */
	public function whereLt($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '<', $value), $type);
	}

	/**
	 * WHERE <= condition
	 *
	 * @param array $column
	 * @param string|int $value
	 * @param string $type
	 * @return Query
	 */
	public function whereLte($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '<=', $value), $type);
	}

	/**
	 * WHERE IN condition
	 *
	 * @param array $column
	 * @param array $values
	 * @param string $type
	 * @return Query
	 */
	public function whereIn($column, $values, $type = 'AND')
	{
		return $this->where(array($column, 'IN', $values), $type);
	}

	/**
	 * WHERE NOT INcondition
	 *
	 * @param array $column
	 * @param array $values
	 * @param string $type
	 * @return Query
	 */
	public function whereNotIn($column, $values, $type = 'AND')
	{
		return $this->where(array($column, 'NOT IN', $values), $type);
	}

	/**
	 * WHERE NULL condition
	 *
	 * @param array $column
	 * @param string $type
	 * @return Query
	 */
	public function whereNull($column, $type = 'AND')
	{
		return $this->where(array($column, 'IS NULL'), $type);
	}

	/**
	 * WHERE NOT NULL condition
	 *
	 * @param array $column
	 * @param string $type
	 * @return Query
	 */
	public function whereNotNull($column, $type = 'AND')
	{
		return $this->where(array($column, 'IS NOT NULL'), $type);
	}

	/**
	 * WHERE BETWEEN condition
	 *
	 * @param array $column
	 * @param array $values
	 * @param string $type
	 * @return Query
	 */
	public function whereBetween($column, $values, $type = 'AND')
	{
		return $this->where(array($column, 'BETWEEN', $values), $type);
	}

	/**
	 * Add a table join (INNER, LEFT OUTER, RIGHT OUTER, FULL OUTER, CROSS)
	 * array('user.id', '=', 'profile.user_id') will compile to ON `user`.`id` = `profile`.`user_id`
	 *
	 * @param string $type, will be prepended to JOIN
	 * @param string $table, should be the name of the table to join to
	 * @param string $constraint, may be either a string or an array with three elements. If it
	 * is a string, it will be compiled into the query as-is, with no escaping. The
	 * recommended way to supply the constraint is as an array with three elements:
	 * array(column1, operator, column2)
	 * @param string $alias, table alias for the joined table
	 * @return Query
	 */
	protected function addJoin($type, $table, $constraint, $alias = null)
	{
		// Add table alias if present
		$table = null === $alias ? trim($table) : trim($table) . ' ' . trim($alias);

		// Build the constraint
		if (is_string($constraint)) {
			$this->join[] = trim($type) . ' JOIN' . ' ' . $table . ' ON (' . trim($constraint) . ')';
		} elseif (is_array($constraint) && count($constraint) == 3) {
			$this->join[] = trim($type) . ' JOIN' . ' ' . $table
				. ' ON (' . trim($constraint[0]) . ' ' . trim($constraint[1]) . ' ' . trim($constraint[2]) . ')';
		}

		return $this;
	}

	/**
	 * Add a simple JOIN
	 *
	 * @param string $table
	 * @param string $constraint
	 * @param string $alias
	 * @return Query
	 */
	public function join($table, $constraint, $alias = null)
	{
		return $this->addJoin('INNER', $table, $constraint, $alias);
	}

	/**
	 * Add an INNER JOIN
	 *
	 * @param string $table
	 * @param string $constraint
	 * @param string $alias
	 * @return Query
	 */
	public function innerJoin($table, $constraint, $alias = null)
	{
		return $this->addJoin('INNER', $table, $constraint, $alias);
	}

	/**
	 * Add a LEFT OUTER JOIN
	 *
	 * @param string $table
	 * @param string $constraint
	 * @param string $alias
	 * @return Query
	 */
	public function leftOuterJoin($table, $constraint, $alias = null)
	{
		return $this->addJoin('LEFT OUTER', $table, $constraint, $alias);
	}

	/**
	 * Add an RIGHT OUTER JOIN
	 *
	 * @param string $table
	 * @param string $constraint
	 * @param string $alias
	 * @return Query
	 */
	public function rightOuterJoin($table, $constraint, $alias = null)
	{
		return $this->addJoin('RIGHT OUTER', $table, $constraint, $alias);
	}

	/**
	 * Add an FULL OUTER JOIN
	 *
	 * @param string $table
	 * @param string $constraint
	 * @param string $alias
	 * @return Query
	 */
	public function fullOuterJoin($table, $constraint, $alias = null)
	{
		return $this->addJoin('FULL OUTER', $table, $constraint, $alias);
	}

	/**
	 * Add an CROSS JOIN
	 *
	 * @param string $table
	 * @param string $constraint
	 * @param string $alias
	 * @return Query
	 */
	public function crossJoin($table, $constraint, $alias = null)
	{
		return $this->addJoin('CROSS', $table, $constraint, $alias);
	}

	/**
	 * Add a HAVING clause
	 *
	 * @todo This can have multiple predicates
	 * @param string $condition
	 * @param array $params, bound parameters
	 * @param string $type
	 * @return Query
	 */
	public function having($condition, $params = array(), $type = 'AND')
	{
		// Only allow AND & OR
		$type = strtoupper(trim($type));
		$type = ($type !== 'AND' && $type !== 'OR') ? 'AND' : $type;
		$this->having[] = array($condition, $params, $type);
		return $this;
	}

	/**
	 * ORDER BY columns
	 *
	 * @param array $fields Array of field names to use for sorting
	 * @param string $sort, sort order for single selection
	 * @return Query
	 */
	public function order($fields = array(), $sort = null)
	{
		$defaultSort = 'ASC';
		if (is_array($fields)) {
			foreach ($fields as $field => $sort) {
				// Numeric index - field as array entry, not key/value pair
				if (is_numeric($field)) {
					$field = $sort;
					$sort = $defaultSort;
				}

				$this->order[] = trim($field . ' ' . strtoupper($sort));
			}
		} else {
			$this->order[] = trim($fields . ' ' . (null === $sort ? $defaultSort : $sort));
		}
		return $this;
	}

	/**
	 * GROUP BY clause
	 *
	 * @param mixed $fields, string or array of field names to use for grouping
	 * @return Query
	 */
	public function group($fields = array())
	{
		if (is_array($fields)) {
			foreach ($fields as $field) {
				$this->group[] = trim($field);
			}
		} else {
			$this->group[] = trim($fields);
		}
		return $this;
	}

	/**
	 * Limit executed query to specified amount of records
	 * Implemented at adapter-level for databases that support it
	 *
	 * @param int $limit Number of records to return
	 * @param int $offset Record to start at for limited result set
	 * @return Query
	 */
	public function limit($limit = 20, $offset = null)
	{
		$this->limit = null === $limit ? null : (int) $limit;

		// Only set offset if a value is passed so we dont inadvertently overwrite a value that was set
		if (null !== $offset) {
			$this->offset($offset);
		}
		return $this;
	}

	/**
	 * Offset executed query to skip specified amount of records
	 * Implemented at adapter-level for databases that support it
	 *
	 * @param int $offset Record to start at for limited result set
	 * @return Query
	 */
	public function offset($offset = 0)
	{
		$this->offset = null === $offset ? null : (int) $offset;
		return $this;
	}

/********************/

	/**
	 * SPL Countable function
	 * Called automatically when attribute is used in a 'count()' function call
	 * Caches results when there are no query changes
	 *
	 * @return int
	 */
	public function count()
	{
		$obj = $this;
		// New scope with closure to get only PUBLIC properties of object instance (can't include cache property)
		$cacheKey = function() use($obj) { return sha1(var_export(get_object_vars($obj), true)) . '_count'; };
		$cacheResult = isset($this->cache[$cacheKey()]) ? $this->cache[$cacheKey()] : false;

		// Check cache
		if ($cacheResult) {
			$result = $cacheResult;
		} else {
			// Execute query
			$result = $this->mapper()->connection($this->entityName())->count($this);

			// Set cache
			$this->cache[$cacheKey()] = $result;
		}

		return is_numeric($result) ? $result : 0;
	}

	/**
	 * SPL IteratorAggregate function
	 * Called automatically when attribute is used in a 'foreach' loop
	 *
	 * @return Spot_Query_Set
	 */
	public function getIterator()
	{
		// Execute query and return result set for iteration
		$result = $this->execute();
		return ($result !== false) ? $result : array();
	}
}
