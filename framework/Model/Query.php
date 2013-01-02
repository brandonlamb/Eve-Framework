<?php
namespace Eve\Model;

#class Query implements \Countable, \IteratorAggregate
#abstract class Query extends Entity
#class Query extends Entity
class Query
{
    protected static $typeHandlers = array(
        'string' => '\Eve\Model\Type\String',
        'text' => '\Eve\Model\Type\String',

        'int' => '\Eve\Model\Type\Integer',
        'integer' => '\Eve\Model\Type\Integer',

        'float' => '\Eve\Model\Type\Float',
        'double' => '\Eve\Model\Type\Float',
        'decimal' => '\Eve\Model\Type\Float',

        'bool' => '\Eve\Model\Type\Boolean',
        'boolean' => '\Eve\Model\Type\Boolean',

        'datetime' => '\Eve\Model\Type\Datetime',
        'date' => '\Eve\Model\Type\Datetime',
        'timestamp' => '\Eve\Model\Type\Integer',
        'year' => '\Eve\Model\Type\Integer',
        'month' => '\Eve\Model\Type\Integer',
        'day' => '\Eve\Model\Type\Integer',
    );
    protected static $tableName;
	protected static $connection;

    // Entity data storage
    protected $data = array();
    protected $dataModified = array();

    // Entity error messages (may be present after save attempt)
    protected $errors = array();

    // Query object
    protected $query;

	// Storage for query properties
	protected $select	= array();
	protected $from		= array();
	protected $join		= array();
	protected $where	= array();
	protected $having	= array();
	protected $group	= array();
	protected $order	= array();
	protected $params	= array();
	protected $limit;
	protected $offset;
	protected $entityName;
	protected $statement;
	protected $stmt;

	/**
	 * Create new query builder and set entity name
	 *
	 * @param string $entityName
	 */
	public function __construct(\PDO $connection = null)
	{
		if (null !== $conn) {
			static::connection($conn);
		}

		// Set default from
		$this->from(static::tableName());
	}

    /**
     * Enable isset() for object properties
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]) || isset($this->dataModified[$key]);
    }

    /**
     * Getter for field properties
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($field)
    {
        $v = null;

        if (isset($this->dataModified[$field])) {
            $v =  $this->dataModified[$field];
        } elseif (isset($this->data[$field])) {
            $v = $this->data[$field];
        } elseif (method_exists($this, 'get' . $field)) {
            $method = 'get' . $field;
            $v = $this->$method();
        }

        if (null !== $v) {
            $fields = $this->fields();
            if (isset($fields[$field])) {
                // Ensure value is get with type handler
                $typeHandler = static::typeHandler($fields[$field]['type']);
                $v = $typeHandler::get($this, $v);
            }
        }

        return $v;
    }

    /**
     * Setter for field properties
     *
     * @param string $field
     * @param mixed  $value
     */
    public function __set($field, $value)
    {
        $fields = $this->fields();
        if (isset($fields[$field])) {
            // Ensure value is set with type handler
            $typeHandler = static::typeHandler($fields[$field]['type']);
            $value = $typeHandler::set($this, $value);
        } elseif (method_exists($this, 'set' . $field)) {
            $method = 'set' . $field;
            $this->$method($value);
        } else {
            $this->dataModified[$field] = $value;
        }
    }

    /**
     * String representation of the class
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__;
    }

	/**
	 * Named connection getter/setter
	 */
	public static function connection(\PDO $connection = null)
	{
		if (null !== $connection) {
			static::$connection = $connection;
		}

		return static::$connection;
	}

    /**
     * Return query builder
     *
     * @param  string $className
     * @return Query
     */
    public static function model($className = __CLASS__)
    {
        $query = new \Eve\Model\Query($className);
        $query->from($className::$tableName);

        return $query;
    }

    /**
     * Table name getter/setter
     *
     * @param  string $value
     * @return string
     */
    public static function tableName($value = null)
    {
        if (null !== $value) {
            static::$tableName = $value;
        }

        return static::$tableName;
    }

    /**
     * Return defined fields of the entity
     *
     * @return array
     */
    public static function fields()
    {
        return array();
    }

    /**
     * Return defined fields of the entity
     *
     * @return array
     */
    public static function relations()
    {
        return array();
    }

    /**
     * Get type handler class by type
     *
     * @param  string                 $type Field type (i.e. 'string' or 'int', etc.)
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
     * Set all field values to their defualts or null
     */
    protected function initFields()
    {
        $fields = static::fields();
        foreach ($fields as $field => $opts) {
            if (!isset($this->data[$field])) {
                $this->data[$field] = isset($opts['default']) ? $opts['default'] : null;
            }
        }
    }

    /**
     * Gets and sets data on the current entity
     */
    public function data($data = null, $modified = true)
    {
        // GET
        if (null === $data || !$data) {
            return array_merge($this->data, $this->dataModified);
        }

        // SET
        if (is_object($data) || is_array($data)) {
            $fields = $this->fields();
            foreach ($data as $k => $v) {
                // Ensure value is set with type handler if Entity field type
                if (isset($fields[$k])) {
                    $typeHandler = static::typeHandler($fields[$k]['type']);
                    $v = $typeHandler::set($this, $v);
                }

                if (true === $modified) {
                    $this->dataModified[$k] = $v;
                } else {
                    $this->data[$k] = $v;
                }
            }

            return $this;
        } else {
            throw new \InvalidArgumentException(__METHOD__ . ' Expected array or object input - ' . gettype($data) . ' given');
        }
    }

    /**
     * Return array of field data with data from the field names listed removed
     *
     * @param array List of field names to exclude in data list returned
     */
    public function dataExcept(array $except)
    {
        return array_diff_key($this->data(), array_flip($except));
    }

    /**
     * Gets data that has been modified since object construct,
     * optionally allowing for selecting a single field
     */
    public function dataModified($field = null)
    {
        if (null !== $field) {
            return isset($this->dataModified[$field]) ? $this->dataModified[$field] : null;
        }

        return $this->dataModified;
    }

    /**
     * Gets data that has not been modified since object construct,
     * optionally allowing for selecting a single field
     */
    public function dataUnmodified($field = null)
    {
        if (null !== $field) {
            return isset($this->data[$field]) ? $this->data[$field] : null;
        }

        return $this->data;
    }

    /**
     * Returns true if a field has been modified.
     * If no field name is passed in, return whether any fields have been changed
     */
    public function isModified($field = null)
    {
        if (null !== $field) {
            if (isset($this->dataModified[$field])) {
                return $this->dataModified[$field] != $this->data[$field];
            } elseif (isset($this->data[$field])) {
                return false;
            } else {
                return null;
            }
        }

        return count($this->dataModified) > 0;
    }

    /**
     * Alias of self::data()
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data();
    }

    /**
     * Check if any errors exist
     *
     * @param  string  $field OPTIONAL field name
     * @return boolean
     */
    public function hasErrors($field = null)
    {
        if (null !== $field) {
            return isset($this->errors[$field]) ? count($this->errors[$field]) > 0 : false;
        }

        return count($this->errors) > 0;
    }

    /**
     * Error message getter/setter
     *
     * @param $field string|array String return errors with field key, array sets errors
     * @return self|array|boolean Setter return self, getter returns array or boolean if key given and not found
     */
    public function errors($msgs = null)
    {
        if (is_string($msgs)) {
            // Return errors for given field
            return isset($this->errors[$msgs]) ? $this->errors[$msgs] : array();
        } elseif (is_array($msgs)) {
            // Set error messages from given array
            $this->errors = $msgs;
        }

        return $this->errors;
    }

    /**
     * Add an error to error messages array
     *
     * @param string $field Field name that error message relates to
     * @param mixed  $msg   Error message text - String or array of messages
     */
    public function error($field, $msg)
    {
        if (is_array($msg)) {
            // Add array of error messages about field
            foreach ($msg as $msgx) {
                $this->errors[$field][] = $msgx;
            }
        } else {
            // Add to error array
            $this->errors[$field][] = $msg;
        }
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
	 * @param  mixed       $columns, string or array of clumns
	 * @param  string|null $alias,   alias for single column selection
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
	 * @param  mixed $tables, string or array of tables
	 * @param  bool  $alias,  table alias
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
	 * @param array  $conditions Array of conditions for this clause
	 * @param string $type       Keyword that will separate each condition - 'AND', 'OR'
	 * @param string $setType    Keyword that will separate the whole set of conditions - 'AND', 'OR'
	 */
	public function where(array $conditions = array(), $type = 'AND', $setType = 'AND')
	{
		// Don't add WHERE clause if array is empty (easy way to support dynamic request options that modify current query)
		if (isset($conditions[2])) {
			$where = array();
			$where['conditions'] = array(
				'column'    => $conditions[0],
				'operator'  => $conditions[1],
				'values'    => $conditions[2],
			);
			$where['type'] = $type;
			$where['setType'] = $setType;

			$this->where[] = $where;
		}

		return $this;
	}

	/**
	 * Add a raw where clause
	 *
	 * @return Query
	 */
	public function whereRaw($condition, $type = 'AND')
	{
		return $this->where(array($condition), $type);
	}

	/**
	 * Add a WHERE ... OR
	 *
	 * @return Query
	 */
	public function orWhere(array $conditions = array(), $type = 'AND')
	{
		return $this->where($conditions, $type, 'OR');
	}

	/**
	 * Add a WHERE ... AND
	 *
	 * @return Query
	 */
	public function andWhere(array $conditions = array(), $type = 'AND')
	{
		return $this->where($conditions, $type, 'AND');
	}

	/**
	 * WHERE = condition
	 *
	 * @param  array  $column
	 * @param  mixed  $value
	 * @param  string $type
	 * @return Query
	 */
	public function whereEqual($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '=', $value), $type);
	}

	/**
	 * WHERE != condition
	 *
	 * @param  array  $column
	 * @param  mixed  $value
	 * @param  string $type
	 * @return Query
	 */
	public function whereNotEqual($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '!=', $value), $type);
	}

	/**
	 * WHERE LIKE condition
	 *
	 * @param  array  $column
	 * @param  mixed  $value
	 * @param  string $type
	 * @return Query
	 */
	public function whereLike($column, $value, $type = 'AND')
	{
		return $this->where(array($column, 'LIKE', $value), $type);
	}

	/**
	 * WHERE NOT LIKE condition
	 * @param  array  $column
	 * @param  mixed  $value
	 * @param  string $type
	 * @return Query
	 */
	public function whereNotLike($column, $value, $type = 'AND')
	{
		return $this->where(array($column, 'NOT LIKE', $value), $type);
	}

	/**
	 * WHERE > condition
	 *
	 * @param  array      $column
	 * @param  string|int $value
	 * @param  string     $type
	 * @return Query
	 */
	public function whereGt($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '>', $value), $type);
	}

	/**
	 * WHERE >= condition
	 *
	 * @param  array      $column
	 * @param  string|int $value
	 * @param  string     $type
	 * @return Query
	 */
	public function whereGte($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '>=', $value), $type);
	}

	/**
	 * WHERE < condition
	 *
	 * @param  array      $column
	 * @param  string|int $value
	 * @param  string     $type
	 * @return Query
	 */
	public function whereLt($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '<', $value), $type);
	}

	/**
	 * WHERE <= condition
	 *
	 * @param  array      $column
	 * @param  string|int $value
	 * @param  string     $type
	 * @return Query
	 */
	public function whereLte($column, $value, $type = 'AND')
	{
		return $this->where(array($column, '<=', $value), $type);
	}

	/**
	 * WHERE IN condition
	 *
	 * @param  array  $column
	 * @param  array  $values
	 * @param  string $type
	 * @return Query
	 */
	public function whereIn($column, $values, $type = 'AND')
	{
		return $this->where(array($column, 'IN', $values), $type);
	}

	/**
	 * WHERE NOT INcondition
	 *
	 * @param  array  $column
	 * @param  array  $values
	 * @param  string $type
	 * @return Query
	 */
	public function whereNotIn($column, $values, $type = 'AND')
	{
		return $this->where(array($column, 'NOT IN', $values), $type);
	}

	/**
	 * WHERE NULL condition
	 *
	 * @param  array  $column
	 * @param  string $type
	 * @return Query
	 */
	public function whereNull($column, $type = 'AND')
	{
		return $this->where(array($column, 'IS NULL'), $type);
	}

	/**
	 * WHERE NOT NULL condition
	 *
	 * @param  array  $column
	 * @param  string $type
	 * @return Query
	 */
	public function whereNotNull($column, $type = 'AND')
	{
		return $this->where(array($column, 'IS NOT NULL'), $type);
	}

	/**
	 * WHERE BETWEEN condition
	 *
	 * @param  array  $column
	 * @param  array  $values
	 * @param  string $type
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
	 * @param string $type,       will be prepended to JOIN
	 * @param string $table,      should be the name of the table to join to
	 * @param string $constraint, may be either a string or an array with three elements. If it
	 * is a string, it will be compiled into the query as-is, with no escaping. The
	 * recommended way to supply the constraint is as an array with three elements:
	 * array(column1, operator, column2)
	 * @param  string $alias, table alias for the joined table
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
	 * @param  string $table
	 * @param  string $constraint
	 * @param  string $alias
	 * @return Query
	 */
	public function join($table, $constraint, $alias = null)
	{
		return $this->addJoin('INNER', $table, $constraint, $alias);
	}

	/**
	 * Add an INNER JOIN
	 *
	 * @param  string $table
	 * @param  string $constraint
	 * @param  string $alias
	 * @return Query
	 */
	public function innerJoin($table, $constraint, $alias = null)
	{
		return $this->addJoin('INNER', $table, $constraint, $alias);
	}

	/**
	 * Add a LEFT OUTER JOIN
	 *
	 * @param  string $table
	 * @param  string $constraint
	 * @param  string $alias
	 * @return Query
	 */
	public function leftOuterJoin($table, $constraint, $alias = null)
	{
		return $this->addJoin('LEFT OUTER', $table, $constraint, $alias);
	}

	/**
	 * Add an RIGHT OUTER JOIN
	 *
	 * @param  string $table
	 * @param  string $constraint
	 * @param  string $alias
	 * @return Query
	 */
	public function rightOuterJoin($table, $constraint, $alias = null)
	{
		return $this->addJoin('RIGHT OUTER', $table, $constraint, $alias);
	}

	/**
	 * Add an FULL OUTER JOIN
	 *
	 * @param  string $table
	 * @param  string $constraint
	 * @param  string $alias
	 * @return Query
	 */
	public function fullOuterJoin($table, $constraint, $alias = null)
	{
		return $this->addJoin('FULL OUTER', $table, $constraint, $alias);
	}

	/**
	 * Add an CROSS JOIN
	 *
	 * @param  string $table
	 * @param  string $constraint
	 * @param  string $alias
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
	 * @param  string $condition
	 * @param  array  $params,   bound parameters
	 * @param  string $type
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
	 * @param  array  $fields Array of field names to use for sorting
	 * @param  string $sort,  sort order for single selection
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
	 * @param  mixed $fields, string or array of field names to use for grouping
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
	 * @param  int   $limit  Number of records to return
	 * @param  int   $offset Record to start at for limited result set
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
	 * @param  int   $offset Record to start at for limited result set
	 * @return Query
	 */
	public function offset($offset = 0)
	{
		$this->offset = null === $offset ? null : (int) $offset;

		return $this;
	}

	/**
	 * Return the built SELECT section of the SQL statement
	 *
	 * @return string
	 */
	public function getSelect()
	{
		return 'SELECT ' . (count($this->select) == 0 ? '*' : implode(', ', $this->select)) . ' ';
	}

	/**
	 * Return the built FROM section of the SQL statement
	 *
	 * @return string
	 */
	public function getFrom()
	{
		return count($this->from) == 0? null : 'FROM ' . implode(', ', $this->from) . ' ';
	}

	/**
	 * Return the built JOIN sections of the SQL statement
	 *
	 * @return string
	 */
	public function getJoin()
	{
		return count($this->join) == 0 ? null : implode(', ', $this->join) . ' ';
	}

	/**
	 * Builds an SQL string given conditions
	 * @return string
	 */
	public function getWhere()
	{
		// If there are no conditions, return back
		if (count($this->where) == 0) {
			return;
		}
#echo '<pre>';
#print_r($this->where);
#die('</pre>');
#print_r($this->where);return;

		$sqlStatement = '';
		foreach ($this->where as $where) {
			$condition =& $where['conditions'];

			// Build where clause based on operator
			switch ($condition['operator']) {
				case '=':
				case ':eq':
					$sqlWhere = $condition['column'] . ' = ?';
					break;
				case '>':
				case ':gt':
					$sqlWhere = $condition['column'] . ' > ?';
					break;
				case '<':
				case ':lt':
					$sqlWhere = $condition['column'] . ' < ?';
					break;
				case '>=':
				case ':gte':
					$sqlWhere = $condition['column'] . ' >= ?';
					break;
				case '<=':
				case ':lte':
					$sqlWhere = $condition['column'] . ' <= ?';
					break;
				case ':gt':
					$sqlWhere = $condition['column'] . ' > ?';
					break;
				case '!=':
				case '<>':
				case ':neq':
				case ':not':
					$sqlWhere = $condition['column'] . ' = ?';
					break;
				case ':like':
					$sqlWhere = $condition['column'] . ' LIKE ?';
					break;
				case 'IN':
					$sqlWhere = $condition['column'] . ' IN(' . join(', ', array_fill(0, count($condition['values']), '?')) . ')';
					break;
				case 'BETWEEN':
					$sqlWhere = $condition['column'] . ' BETWEEN ' . join(' AND ', array_fill(0, count($condition['values']), '?'));
					break;
				case 'IS NULL':
					$sqlWhere = $condition['column'] . ' IS NULL';
					break;
				case 'IS NOT NULL':
					$sqlWhere = $condition['column'] . ' IS NOT NULL';
					break;
				default:
					$sqlWhere = $condition['column'] . ' ' . $condition['operator'] . ' ?';
			}

			// If statement has already been started, precede next where with its type separator
			if (!empty($sqlStatement)) {
				$sqlStatement .= ' ' . $where['type'] . ' ';
			}
			$sqlStatement .= $sqlWhere;

			// If values is not an array, just push to boundParams
			if (!is_array($condition['values'])) {
				$this->params[] = $condition['values'];
			} else {
				// Loop through each value and push to boundParams
				foreach ($condition['values'] as $value) {
					if ($condition['operator'] != 'IS NULL' && $condition['operator'] != 'IS NOT NULL') {
						$this->params[] = $value;
					}
				}
			}
		}

		return ($sqlStatement != '') ? 'WHERE ' . $sqlStatement . ' ' : '';
	}

	/**
	 * Return the built ORDER BY section of the SQL statement
	 *
	 * @return string
	 */
	public function getOrder()
	{
		return count($this->order) == 0 ? null : 'ORDER BY ' . implode(', ', $this->order) . ' ';
	}

	/**
	 * Return the built GROUP BY section of the SQL statement
	 *
	 * @return string
	 */
	public function getGroup()
	{
		return count($this->group) == 0 ? null :  'GROUP BY ' . implode(', ', $this->group) . ' ';
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
	 * Return the built OFFSET section of the SQL statement
	 *
	 * @return string
	 */
	public function getOffset()
	{
		return null === $this->offset ? null :  'OFFSET ' . (int) $this->offset . ' ';
	}

	/**
	 * Return an object of the next result row
	 *
	 * @return false|array
	 */
	public function fetchRow()
	{
		return ($row = $this->stmt->fetch(\PDO::FETCH_ASSOC)) ? $row : false;
	}

	/**
	 * Calls fetch on stmt
	 *
	 * @return array
	 */
	public function fetchRows()
	{
		$rows = array();
		while ($row = $this->stmt->fetch(\PDO::FETCH_ASSOC)) {
			$rows[] = $row;
		}

		// Reset where conditions and values and default results columns
#		$this->reset();

		// Close the cursor, allowing the statement to be executed again
		$this->_stmt->closeCursor();

		return $rows;
	}

	/**
	 * Fetch a single result back from query and execute it. If you pass an ID as a parameter to
	 * this method this will perform a primary key lookup on the table.
	 *
	 * @param  int        $primary
	 * @return null|mixed
	 */
	public function fetchOne($primary = null)
	{
		// If id was passed, add to where clause
		if (null !== $primary) {
			$this->wherePrimary($primary);
		}

		// Run the query, fetch a row
		$row = $this->limit(1)->read()->fetchRow();

		// If we got results, return populated self, or hyrdated new instance
		return (false !== $row) ? $this->data($row) : false;
	}

	/**
	 * Tell the ORM that you are expecting multiple results from your query, and execute it. Will return an array of
	 * instances of the ORM class, or an empty array if no rows were returned.
	 * @return void
	 */
	public function fetchMany()
	{
		$rows = $this->read()->fetchRows();
		$this->collection = array_map(array($this, 'create'), $rows);
	}

	/**
	 * Execute query. Return an array of rows as associative arrays
	 *
	 * @return Query
	 */
	public function execute()
	{
		try {
			// Prepare the statement
			$this->stmt = $this->connection()->prepare($this->statement);

			// Execute the prepared statement
			$this->stmt->execute($this->params);
		} catch (PDOException $e) {
			throw new \Exception(__LINE__ . ' ' . $e->getMessage() . " : " . $this->_statement);
		}

		// Reset conditions/parameters
		$this->reset();

		return $this;
	}

	/**
	 *
	 */
	public function read()
	{
		$this->statement = $this->getSelect()
			. $this->getFrom()
			. $this->getJoin()
			. $this->getWhere()
			. $this->getOrder()
			. $this->getGroup()
			. $this->getLimit();
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
		$cacheKey = function() use ($obj) { return sha1(var_export(get_object_vars($obj), true)) . '_count'; };
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
