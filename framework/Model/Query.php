<?php
namespace Eve\Model;

#class Query implements \Countable, \IteratorAggregate
class Query
{
	// Storage for query properties
	protected $select		= array();
	protected $from			= array();
	protected $join			= array();
	protected $where		= array();
	protected $having		= array();
	protected $group		= array();
	protected $order		= array();
	protected $search		= array();
	protected $params		= array();
	protected $limit;
	protected $offset;

	/**
	 * Returns the static model of the specified AR class.
	 * Query::model()->select('col1')->from('tbl')->where('col1 = ?', $val)->fetchOne();
	 *
	 * @param string $className active record class name.
	 * @return Query
	 */
	public static function model($className = __CLASS__)
	{
		return new $className();
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
	 * Return the built SELECT section of the SQL statement
	 *
	 * @return string
	 */
	public function getSelect()
	{
		return 'SELECT ' . (count($this->select) == 0 ? '*' : implode($this->select, ', ')) . ' ';
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
	 * Return the built FROM section of the SQL statement
	 *
	 * @return string
	 */
	public function getFrom()
	{
		return 'FROM ' . implode($this->from, ', ') . ' ';
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
	public function orWhere(array $conditions = array(), $type = 'AND')
	{
		return $this->where($conditions, $type, 'OR');
	}

	public function andWhere(array $conditions = array(), $type = 'AND')
	{
		return $this->where($conditions, $type, 'AND');
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
	 * Return the built JOIN sections of the SQL statement
	 *
	 * @return string
	 */
	public function getJoin()
	{
		return (count($this->join) == 0 ? null : implode($this->join, ', ')) . ' ';
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
	 * Return the built ORDER BY section of the SQL statement
	 *
	 * @return string
	 */
	public function getOrder()
	{
		return count($this->order) == 0 ? null : 'ORDER BY ' . implode($this->order, ', ') . ' ';
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
	 * Return the built GROUP BY section of the SQL statement
	 *
	 * @return string
	 */
	public function getGroup()
	{
		return count($this->group) == 0 ? null :  'GROUP BY ' . implode($this->group, ', ') . ' ';
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
	 * Return the built LIMIT section of the SQL statement
	 *
	 * @return string
	 */
	public function getLimit()
	{
		return null === $this->limit ? null :  'LIMIT ' . (int) $this->limit . ' ';
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

	/**
	 * Return the built OFFSET section of the SQL statement
	 *
	 * @return string
	 */
	public function getOffset()
	{
		return null === $this->offset ? null :  'OFFSET ' . (int) $this->offset . ' ';
	}







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


#$query = new Query();
#$query->from(array('User u'));
