<?php
namespace Eve;

class Model
{
    // Cache prefix
    protected $_cachePrefix = 'Eve_Orm';

    // Cache lifetime
    protected $_cacheLifetime = 86400;

    // Cache module
    protected $_cache = null;

    // @var bool, row loaded
    protected $_loaded;

    // @var array, model fields
    protected $_fields = array();

    // @var array, model relations
    protected $_relations = array();

    // @var array, model data
    protected $_data = array();

    // @var array, changed data fields
    protected $_dataModified = array();

    // @var array, ignored getter properties
    protected $_getterIgnore = array();

    // @var array ignored setter properties
    protected $_setterIgnore = array();

    // @var resource, Database connection
    protected $_conn;

    // @var string, Database name (namespace in config, should be overloaded by child)
    protected $_database = 'db1';

    // @var string, Table name (should be overloaded by child)
    protected $_table;

    // @var string, Alias for the table to be used in SELECT queries
    protected $_tableAlias;

    // @var array, FROM tables
    protected $_tables = array();

    // @var mixed The primary key for the table (should be overloaded by child)
    protected $_primaryKey;

    // @var bool, Logging flag
    protected $_logging = false;

    // @var array, Query log
    protected static $_queryLog = array();

    // Last query run, only populated if logging is enabled
    protected static $_lastQuery;

    // @var array, Query select columns to select in the result
    protected $_queryColumns = array();

    // Query HAVING conditions
    protected $_havingConditions = array();

    // @var array, Query WHERE condition clauses
    protected $_whereConditions = array();

    // @var array, ORDER BY clauses
    protected $_orderBy = array();

    // @var array, GROUP BY clauses
    protected $_groupBy = array();

    // Query limit
    protected $_limit;

    // Query offset
    protected $_offset;

    // Query statement built up by helper methods
    protected $_statement;

    // Query statement object
    protected $_stmt;

    // @var array, The bound parameters array, you have to pass this to pdo to swap out your named parameters
    protected $_boundParams = array();

    // Join sources
    protected $_joinSources = array();

    // Collection array, this holds results for a fetchMany call
    protected $_collection = array();

    /**
     * Constructor
     * @param Database | object $conn
     */
    public function __construct($conn = null)
    {
        // Get the database connection
        $this->_conn = Yaf_Registry::get('database')->getConnection($this->_database);

        // Set PDO connection's error mode
        $this->_conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Run model init()
        $this->init();
    }

    /**
     * Enable isset() for object properties
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return ($this->$key !== null) ? true : false;
    }

    /**
     * Getter for model properties
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        // Check for custom getter method (override)
        $getMethod = 'get' . ucwords($key);

        if (method_exists($this, $getMethod) && !array_key_exists($key, $this->_getterIgnore)) {
            // Tell this function to ignore the overload on further calls for this variable
            $this->_getterIgnore[$key] = 1;

            // Call custom getter
            $result = $this->$getMethod();

            // Remove ignore rule
            unset($this->_getterIgnore[$key]);

            return $result;
        } else {
            // Handle default way
            if (isset($this->_dataModified[$key])) {
                return $this->_dataModified[$key];
            } elseif (isset($this->_data[$key])) {
                return $this->_data[$key];
            } else {
                return null;
            }
        }
    }

    /**
     * Setter for model properties
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        // Check for custom setter method (override)
        $setMethod = 'set' . ucwords($key);

        // Run value through a filter call if set
        if (isset($this->_fields[$key]['filter'])) {
            $value = call_user_func($this->_fields[$key]['filter'], $value);
        }

        if (method_exists($this, $setMethod) && !array_key_exists($key, $this->_setterIgnore)) {
            // Tell this function to ignore the overload on further calls for this variable
            $this->_setterIgnore[$key] = 1;

            // Call custom setter
            $result = $this->$setMethod($value);

            // Remove ignore rule
            unset($this->_setterIgnore[$key]);

            return $result;
        } else {
            // Handle default way
            if ($this->_loaded) {
                $this->_dataModified[$key] = $value;
            } else {
                $this->_data[$key] = $value;
            }
        }
    }

    /**
     * Get model property. Pass to magic __get()
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        return __get($key);
    }

    /**
     * Set model property. Pass to magic __set() then return $this
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->__set($key, $value);

        return $this;
    }

    /**
     * Override this init() method in the model
     */
    public function init() {}

    /**
     * Mark row as 'loaded'. Any data set after row is loaded will be modified data
     * @param boolean $value
     * @return $this|bool
     */
    public function loaded($value = null)
    {
        if (null !== $value) {
            $this->_loaded = (bool) $value;

            return $this;
        }

        return (bool) $this->_loaded;
    }

    /**
     * Reset the object
     * @return $this
     */
    public function reset()
    {
        $this->_queryColumns = array();
        $this->_tables = array();
        $this->_joinSources = array();
        $this->_havingConditions = array();
        $this->_whereConditions = array();
        $this->_groupBy = array();
        $this->_orderBy = array();
        $this->_limit = null;
        $this->_offset = null;
        $this->_boundParams = array();
#		$this->_collection = array();

        return $this;
    }

    /**
     * Connection resource getter/setter
     * @param  resource $value
     * @return resource
     */
    public function connection($value = null)
    {
        if (null !== $value) {
            $this->_conn = $value;

            return $this;
        } elseif (!$this->_conn) {
            throw new Exception('Database not connected');
        } else {
            return $this->_conn;
        }
    }

    /**
     * Database name getter/setter
     * @param  string $value
     * @return string
     */
    public function database($value = null)
    {
        if (null !== $value) {
            $this->_database = $value;

            return $this;
        }

        return $this->_database;
    }

    /**
     * Table name getter/setter
     * @param  string $value
     * @return string
     */
    public function table($value = null)
    {
        if (null !== $value) {
            $this->_table = $value;

            return $this;
        }

        return $this->_table;
    }

    /**
     * Table alias getter/setter
     * @param  string $value
     * @return string
     */
    public function tableAlias($value = null)
    {
        if (null !== $value) {
            $this->_tableAlias = $value;

            return $this;
        }

        return $this->_tableAlias;
    }

    /**
     * Primary key getter/setter
     * @param  string $value
     * @return string
     */
    public function primaryKey($value = null)
    {
        if (null !== $value) {
            $this->_primaryKey = $value;

            return $this;
        }

        return $this->_primaryKey;
    }

    /**
     * Data getter/setter
     * @param mixed $value
     * @return $this|array
     */
    public function data($value = null)
    {
        if (null !== $value) {
            if (is_object($value) || is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->$k = $v;
                }
                $this->_loaded = true;

                return $this;
            } else {
                throw new InvalidArgumentException(__METHOD__ . " Expected array or object input - " . gettype($value) . " given");
            }
        } else {
            return $this->toArray();
        }
    }

    /**
     * Modified data getter/setter. Returns array of key => value pairs for row data
     * @param  mixed $value
     * @return array
     */
    public function dataModified($value = null)
    {
        if (null !== $value) {
            if (is_object($value) || is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->_dataModified[$k] = $v;
                }

                return $this;
            } else {
                throw new InvalidArgumentException(__METHOD__ . " Expected array or object input - " . gettype($value) . " given");
            }
        } else {
            return $this->_dataModified;
        }
    }

    /**
     * Returns array of key => value pairs for row data
     * @return array
     */
    public function toArray()
    {
        if (func_num_args() === 0) {
            return array_merge($this->_data, $this->_dataModified);
        }
        $args = func_get_args();

        return array_intersect_key(array_merge($this->_data, $this->_dataModified), array_flip($args));
    }

    /**
     * Return JSON-encoded row (convenience function)
     * Only works for basic objects right now
     * @return string
     * @todo Return fully mapped row objects with related rows (has one, has many, etc)
     */
    public function toJson()
    {
        return json_encode($this->data());
    }

    /**
     * Get formatted fields with all neccesary array keys and values.
     * Merges defaults with defined field values to ensure all options exist for each field.
     * @param  array $fields
     * @return array Defined fields plus all defaults for full array of all possible options
     */
    public function fields($fields = array())
    {
        if ($this->_fields) {
            $returnFields = $this->_fields;
        } else {
            // Default settings for all fields
            $fieldDefaults = array(
                'column'	=> null,
                'default'	=> null,
                'length'	=> null,
                'validator'	=> null,
                'filter'	=> null,
                'required'	=> false,
                'null'		=> true,
                'primary'	=> false,
                'relation'	=> false
            );

            $returnFields = array();
            foreach ($fields as $fieldName => $fieldOpts) {
                // Merge model specific options with defaults
                $fieldOpts = array_merge($fieldDefaults, $fieldOpts);

                // Store primary key if set
                if ($fieldOpts['primary'] === true) { $this->_primaryKey = $fieldName; }

                // Store relations (and remove them from the mix of regular fields)
                if ($fieldOpts['relation'] !== false) {
                    $this->_relations[$fieldName] = $fieldOpts;

                    // skip, not a field
                    continue;
                }

                $returnFields[$fieldName] = $fieldOpts;
            }
            $this->_fields = $returnFields;
        }

        return $returnFields;
    }

    /**
     * Check if field exists in defined fields
     * @param  string $key
     * @return array
     */
    public function fieldExists($key)
    {
        return array_key_exists($key, $this->fields());
    }

    /**
     * Logging flag getter/setter
     * @param bool $value
     * @return $this|bool
     */
    public function logging($value = null)
    {
        if (null !== $value) {
            $this->_logging = (bool) $value;

            return $this;
        } else {
            return $this->_logging;
        }
    }

    /**
     * Log query
     * @param  string $sql
     * @param  array  $data
     * @return void
     */
    public function logQuery($sql, $data = null)
    {
        self::$_queryLog[] = array('query' => $sql, 'data' => $data);
        if ($this->_logging === true) { $this->saveQueryLog($sql, $data); }
    }

    /**
     * Add a query to the internal query log. Only works if the 'logging' config option is set to true.
     * This works by manually binding the parameters to the query - the query isn't executed like this (PDO normally passes the query and
     * parameters to the database which takes care of the binding) but doing it this way makes the logged queries more readable.
     * @todo Hook into queryLog()
     * @param  string $sql
     * @param  array  $values
     * @return void
     */
    protected function saveQueryLog($sql, $values = array())
    {
        $logFile = Yaf_Application::app()->getConfig()->application->logs . '/query.log';

        if (count($values) > 0) {
            // Escape the $values
            $values = array_map('mysql_escape_string', $values);

            // Replace placeholders in the query for vsprintf
            $sql = str_replace('?', '\'%s\'', $sql);

            // Replace the question marks in the query with the parameters
            $boundQuery = vsprintf($sql, $values);
        } else {
            $boundQuery = $sql;
        }

        // Log to application log if it exists
        Yaf_Registry::get('logger')->info($boundQuery);
#		if (file_exists($logFile)) { error_log($boundQuery . "\n", 3, $logFile); }

        self::$_lastQuery = $boundQuery;
    }

    /**
     * Get the last query executed. Only works if the 'logging' config option is set to true. Otherwise this will return null.
     * @return string
     */
    public function lastQuery()
    {
        return self::$_lastQuery;
    }

    /**
     * Prints all executed SQL queries - useful for debugging
     * @return void
     */
    public function debug()
    {
        return self::$_queryLog;
        echo '<script>console.log("Executed ' . $this->queryCount() . " queries:\n";
        foreach (self::$_queryLog as $query) {
            echo $query . "\n";
        }
        echo '");</script>' . "\n";
    }

    /**
     * Get count of all queries that have been executed
     * @return int
     */
    public function queryCount()
    {
        return count(self::$_queryLog);
    }

    /**
     * Find records with custom SQL query
     * @todo Clean this up to actually return results
     * @param  string     $sql   SQL query to execute
     * @param  array      $binds Array of bound parameters to use as values for query
     * @throws Exception
     * @return array|bool
     */
    public function query($sql, array $binds = array(), $isRead = true)
    {
        // Add query to log
        $this->logQuery($sql, $binds);

        // Prepare and execute query
        if ($stmt = $this->connection()->prepare($sql)) {
            $results = $stmt->execute($binds);
            if ($isRead === false) { return true; }

            return ($results) ? $this->getResultSet($stmt) : false;
        } else {
            throw new $this->_exceptionClass(__METHOD__ . " Error: Unable to execute SQL query - failed to create prepared statement from given SQL");
        }
    }

    /**
     * Add a column to the list of columns returned by the SELECT query.
     * Defaults to '*'. Second optional argument is the alias to return the column as.
     * @param string $columns
     * @param string $alias
     * @return $this
     */
    public function select($columns = '*', $alias = null)
    {
        if (null !== $alias && is_string($columns)) {
            $this->_queryColumns[] = $columns . ' AS ' . $alias;

            return $this;
        }

        // If $columns is array push to queryColumns, otherwise split by string
        if (is_array($columns)) {
            // $columns is array, push to queryColumns
            $this->_queryColumns = array_merge($this->_queryColumns, $columns);
        } else {
            // Split possible multiple selections by comma and push to queryColumns
            $this->_queryColumns = array_merge($this->_queryColumns, explode(',', $columns));
        }

        return $this;
    }

    /**
     * Add a COUNT select statement to the query. Will return an integer representing the number of rows returned.
     * @param  string $alias
     * @return void
     */
    public function count($alias = 'count')
    {
        $this->select('COUNT(*)', $alias);
    }

    /**
     * Adds a table to the from selection
     * @param string $table
     * @param string $alias
     * @return $this
     */
    public function from($table, $alias = null)
    {
        $this->_tables[] = (null !== $alias) ? $table . ' ' . $alias : $table;

        return $this;
    }

    /**
     * @param string $type,       INNER, LEFT OUTER, CROSS etc. Will be prepended to JOIN.
     * @param string $table,      should be the name of the table to join to.
     * @param string $constraint, may be either a string or an array with three elements. If it
     * is a string, it will be compiled into the query as-is, with no escaping. The
     * recommended way to supply the constraint is as an array with three elements:
     * @param string $alias, table alias for the joined table
     * column1, operator, column2
     * @return $this
     *
     * Example:
     * array('user.id', '=', 'profile.user_id') will compile to ON `user`.`id` = `profile`.`user_id`
     */
    protected function _addJoinSource($type, $table, $constraint, $alias = null)
    {
        $type = trim($type . ' JOIN');

        // Add table alias if present
        if (null !== $alias) { $table .= ' ' . $alias; }

        // Build the constraint
        if (is_array($constraint)) {
            list($column1, $operator, $column2) = $constraint;
            $constraint = $column1 . ' ' . $operator . ' ' . $column2;
        }

        $this->_joinSources[] = $type . ' ' . $table . ' ON (' . $constraint . ')';

        return $this;
    }

    /**
     * Add a simple JOIN source to the query
     * @param string $table
     * @param string $constraint
     * @param string $alias
     * @return $this
     */
    public function join($table, $constraint, $alias = null)
    {
        return $this->_addJoinSource('', $table, $constraint, $alias);
    }

    /**
     * Add an INNER JOIN source to the query
     * @param string $table
     * @param string $constraint
     * @param string $alias
     * @return $this
     */
    public function innerJoin($table, $constraint, $alias = null)
    {
        return $this->_addJoinSource('INNER', $table, $constraint, $alias);
    }

    /**
     * Add a LEFT OUTER JOIN source to the query
     * @param string $table
     * @param string $constraint
     * @param string $alias
     * @return $this
     */
    public function leftOuterJoin($table, $constraint, $alias = null)
    {
        return $this->_addJoinSource('LEFT OUTER', $table, $constraint, $alias);
    }

    /**
     * Add an RIGHT OUTER JOIN source to the query
     * @param string $table
     * @param string $constraint
     * @param string $alias
     * @return $this
     */
    public function rightOuterJoin($table, $constraint, $alias = null)
    {
        return $this->_addJoinSource('RIGHT OUTER', $table, $constraint, $alias);
    }

    /**
     * Add an FULL OUTER JOIN source to the query
     * @param string $table
     * @param string $constraint
     * @param string $alias
     * @return $this
     */
    public function fullOuterJoin($table, $constraint, $alias = null)
    {
        return $this->_addJoinSource('FULL OUTER', $table, $constraint, $alias);
    }

    /**
     * Add an CROSS JOIN source to the query
     * @param string $table
     * @param string $constraint
     * @param string $alias
     * @return $this
     */
    public function crossJoin($table, $constraint, $alias = null)
    {
        return $this->_addJoinSource('CROSS', $table, $constraint, $alias);
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $values
     * @param string       $operator
     * @param string       $type     Keyword that will separate each condition - "AND", "OR"
     * @return $this
     */
    public function where($column, $values, $operator = '=', $type = 'AND')
    {
        // Create single entry array if values is a string
        if (!is_array($values)) { $values = array($values); }

        $this->_whereConditions[] = array(
            'column'		=> $column,
            'operator'		=> $operator,
            'values'		=> $values,
            'type'			=> $type,
        );

        return $this;
    }
    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $operator
     * @return $this
     */
    public function orWhere($column, $value, $operator = '=')
    {
        return $this->where($column, $value, $operator, 'OR');
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $operator
     * @return $this
     */
    public function andWhere($column, $value, $operator = '=')
    {
        return $this->where($column, $value, $operator, 'AND');
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $type
     * @return $this
     */
    public function whereEqual($column, $value, $type = 'AND')
    {
        return $this->where($column, $value, '=', $type);
    }

    /**
     * WHERE conditions
     * @param string $value
     * @param string $operator
     * @param string $type
     * @return $this
     */
    public function wherePrimary($value, $operator = '=', $type = 'AND')
    {
        return $this->where($this->_primaryKey, $value, $operator, $type);
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $type
     * @return $this
     */
    public function whereNotEqual($column, $value, $type = 'AND')
    {
        return $this->where($column, $value, '!=', $type);
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $type
     * @return $this
     */
    public function whereLike($column, $value, $type = 'AND')
    {
        return $this->where($column, $value, 'LIKE', $type);
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $type
     * @return $this
     */
    public function whereNotLike($column, $value, $type = 'AND')
    {
        return $this->where($column, $value, 'NOT LIKE', $type);
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $type
     * @return $this
     */
    public function whereGt($column, $value, $type = 'AND')
    {
        return $this->where($column, $value, '>', $type);
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $type
     * @return $this
     */
    public function whereLt($column, $value, $type = 'AND')
    {
        return $this->where($column, $value, '<', $type);
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $type
     * @return $this
     */
    public function whereGte($column, $value, $type = 'AND')
    {
        return $this->where($column, $value, '>=', $type);
    }

    /**
     * WHERE conditions
     * @param array        $column
     * @param string|array $value
     * @param string       $type
     * @return $this
     */
    public function whereLte($column, $value, $type = 'AND')
    {
        return $this->where($column, $value, '<=', $type);
    }

    /**
     * WHERE conditions
     * @param array  $column
     * @param string $values
     * @param string $type
     * @return $this
     */
    public function whereIn($column, $values, $type = 'AND')
    {
        return $this->where($column, $values, 'IN', $type);
    }

    /**
     * WHERE conditions
     * @param array  $column
     * @param array  $values
     * @param string $type
     * @return $this
     */
    public function whereNotIn($column, $values, $type = 'AND')
    {
        return $this->where($column, $values, 'NOT IN', $type);
    }

    /**
     * WHERE conditions
     * @param array  $column
     * @param string $type
     * @return $this
     */
    public function whereNull($column, $type = 'AND')
    {
        return $this->where($column, null, 'IS NULL', $type);
    }

    /**
     * WHERE conditions
     * @param array  $column
     * @param string $type
     * @return $this
     */
    public function whereNotNull($column, $type = 'AND')
    {
        return $this->where($column, null, 'IS NOT NULL', $type);
    }

    /**
     * WHERE conditions
     * @param array  $column
     * @param array  $values
     * @param string $type
     * @return $this
     */
    public function whereBetween($column, $values, $type = 'AND')
    {
        return $this->where($column, $values, 'BETWEEN', $type);
    }

    /**
     * Add a having clause
     * @param string $condition
     * @return $this
     */
    public function having($condition = null)
    {
        if (null !== $condition) {
            $this->_havingConditions[] = $condition;

            return $this;
        }

        return join(' AND ', $this->_havingConditions);
    }

    /**
     * Add ORDER BY column value clause
     * @param string $column
     * @param string $value
     * @return $this|string
     */
    public function orderBy($column = null, $value = 'DESC')
    {
        if (null !== $column) {
            $this->_orderBy[] = $column . ' ' . $value;

            return $this;
        } else {
            return join(', ', $this->_orderBy);
        }
    }

    /**
     * Add ORDER BY column DESC clause
     * @param string $column
     * @return $this
     */
    public function orderByDesc($column)
    {
        $this->_orderBy[] = $column . ' DESC';

        return $this;
    }

    /**
     * Add ORDER BY column ASC clause
     * @param string $column
     * @return $this
     */
    public function orderByAsc($column)
    {
        $this->_orderBy[] = $column . ' ASC';

        return $this;
    }

    /**
     * Add ORDER BY RAND() clause
     * @return $this
     */
    public function orderByRandom()
    {
        $this->_orderBy[] = 'RAND()';

        return $this;
    }

    /**
     * Add a column to the list of columns to GROUP BY
     * @param string $column
     * @return $this|string
     */
    public function groupBy($column)
    {
        if (null !== $column) {
            $this->_groupBy[] = $column;

            return $this;
        } else {
            return join(', ', $this->_groupBy);
        }
    }

    /**
     * Limit executed query to specified amount of rows
     * @param  int      $limit  Number of records to return
     * @param  int      $offset Row to start at for limited result set
     * @return int|self
     */
    public function limit($limit = null, $offset = null)
    {
        if (null !== $limit) {
            $this->_limit = $limit;
            $this->_offset = (null !== $offset) ? $offset : $this->_offset;

            return $this;
        }

        return $this->_limit;
    }

    /**
     * Add an OFFSET to the query
     * @param  int $value
     * @return int
     */
    public function offset($value = null)
    {
        if (null !== $value) {
            $this->_offset = $value;

            return $this;
        }

        return $this->_offset;
    }

    /**
     * Execute query. Return an array of rows as associative arrays
     * @return $this
     */
    public function read()
    {
        // Build the query
        $this->_statement = $this->statementTop()
            . $this->statementFields()
            . $this->statementTables()
            . $this->statementJoins()
            . $this->statementConditions()
            . $this->statementOrderBy()
            . $this->statementGroupBy()
            . $this->statementLimit()
            . $this->statementOffset();

        return $this->execute();
    }

    /**
     * Execute query. Return an array of rows as associative arrays
     * @return $this
     */
    public function execute()
    {
        // Pass query and values to query logger
        $this->logQuery($this->_statement, $this->_boundParams);

        try {
            // Prepare the statement
            $this->_stmt = $this->connection()->prepare($this->_statement);
#			assert($this->_stmt->errorCode() === '');

            // Execute the prepared statement
            $this->_stmt->execute($this->_boundParams);
#			assert($this->_stmt->errorCode() === '00000');
        } catch (PDOException $e) {
#			throw new \Exception(__LINE__ . ' ' . $e->getMessage() . " : " . $this->lastQuery());
            throw new \Exception(__LINE__ . ' ' . $e->getMessage() . " : " . $this->_statement);
        }

        // Reset conditions/parameters
        $this->reset();

        return $this;
    }

    /**
     * Calls fetch on stmt
     * @return array
     */
    public function fetchRows()
    {
        $rows = array();
        while ($row = $this->_stmt->fetch(\PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }

        // Reset where conditions and values and default results columns
#		$this->reset();

        // Close the cursor, allowing the statement to be executed again
        $this->_stmt->closeCursor();

        return $rows;
    }

    /**
     * Return an object of the next result row
     * @return null|array
     */
    public function fetchRow()
    {
        return ($row = $this->_stmt->fetch(\PDO::FETCH_ASSOC)) ? $row : null;
    }

    /**
     * Fetch a single result back from query and execute it. If you pass an ID as a parameter to
     * this method this will perform a primary key lookup on the table.
     * @param  int        $primary
     * @return null|mixed
     */
    public function fetchOne($primary = null)
    {
        // If id was passed, add to where clause
        if (null !== $primary) { $this->wherePrimary($primary); }

        // Run the query, fetch a row
        $row = $this->limit(1)->read()->fetchRow();

        // If we got results, return populated self, or hyrdated new instance
        return (null !== $row) ? $this->data($row) : null;
    }

    /**
     * Tell the ORM that you are expecting multiple results from your query, and execute it. Will return an array of
     * instances of the ORM class, or an empty array if no rows were returned.
     * @return void
     */
    public function fetchMany()
    {
        $rows = $this->read()->fetchRows();
        $this->_collection = array_map(array($this, '_create'), $rows);
    }

    /**
     * Tell the ORM that you are expecting a single result back from your query, and execute it. Will return a single instance of the ORM class, or false if no
     * rows were returned. As a shortcut, you may supply an ID as a parameter to this method. This will perform a primary key lookup on the table.
     * @param  int   $primary
     * @return array
     */
    public function returnOne($primary = null)
    {
        // If id was passed, add to where clause
        if (null !== $primary) { $this->wherePrimary($primary); }

        // Run the query
        $row = $this->limit(1)->read()->fetchRow();

        // If we got results, return populated self, or hyrdated new instance
        return (!empty($row)) ? $this->_create($row) : false;
    }

    /**
     * Tell the ORM that you are expecting multiple results from your query, and execute it. Will return an array of
     * instances of the ORM class, or an empty array if no rows were returned.
     * @return array
     */
    public function returnMany()
    {
        $rows = $this->read()->fetchRows();

        return array_map(array($this, '_create'), $rows);
    }

    /**
     * Build TOP
     * @return string
     */
    public function statementTop()
    {
        if (null !== $this->_limit) {
            return ($this->_conn->type() == 'sqldrv') ? ' TOP ' . $this->_limit : null;
        }
    }

    /**
     * Return fields as a string for a query statement
     * @return string
     */
    public function statementFields()
    {
        return 'SELECT ' . (count($this->_queryColumns) > 0 ? implode(', ', $this->_queryColumns) : '*');
    }

    /**
     * Return FROM tables as a string
     * @return string
     */
    public function statementTables()
    {
        $current = (null !== $this->_tableAlias) ? $this->_table . ' ' . $this->_tableAlias : $this->_table;

        if (count($this->_tables) > 0) {
            return ' FROM ' . $current . ', ' . join(', ', $this->_tables);
        } else {
            return ' FROM ' . $current;
        }
    }

    /**
     * Build joins
     * @return string
     */
    public function statementJoins()
    {
        // If there are no conditions, return back
        if (count($this->_joinSources) == 0) { return; }

        return ' ' . join(' ', $this->_joinSources);
    }

    /**
     * Builds an SQL string given conditions
     * @return string
     */
    public function statementConditions()
    {
        // If there are no conditions, return back
        if (count($this->_whereConditions) == 0) { return; }

        $sqlStatement = '';
        foreach ($this->_whereConditions as $condition) {
            // Build where clause based on operator
            switch ($condition['operator']) {
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
            if ($sqlStatement != '') { $sqlStatement .= ' ' . $condition['type'] . ' '; }
            $sqlStatement .= $sqlWhere;

            // If values is not an array, just push to boundParams
            if (!is_array($condition['values'])) {
                $this->_boundParams[] = $condition['values'];
            } else {
                // Loop through each value and push to boundParams
                foreach ($condition['values'] as $value) {
                    if ($condition['operator'] != 'IS NULL' && $condition['operator'] != 'IS NOT NULL') {
                        $this->_boundParams[] = $value;
                    }
                }
            }
        }

        return ($sqlStatement != '') ? ' WHERE ' . $sqlStatement : '';
    }

    /**
     * Build ORDER BY
     * @return string
     */
    protected function statementOrderBy()
    {
        if (count($this->_orderBy) > 0) {
            return ' ORDER BY ' . join(', ', $this->_orderBy);
        }
    }

    /**
     * Build GROUP BY
     * @return string
     */
    public function statementGroupBy()
    {
        if (count($this->_groupBy) > 0) {
            return ' GROUP BY ' . join(', ', $this->_groupBy);
        }
    }

    /**
     * Build LIMIT
     * @return string
     */
    public function statementLimit()
    {
        if (null !== $this->_limit) {
            return ($this->_conn->type() == 'mysql') ? ' LIMIT ' . $this->_limit : null;
        }
    }

    /**
     * Build OFFSET
     * @return string
     */
    public function statementOffset()
    {
        if (null !== $this->_offset) {
            return ' OFFSET ' . $this->_offset;
        }
    }

    /**
     * Create an ORM instance from the given row (an associative array of data fetched from the database)
     * @param  array  $row
     * @return object
     */
    protected function _create(array $row)
    {
        $className = get_called_class();
        $instance = new $className();
        $instance->data($row);

        return $instance;
    }

    /**
     * Return the collection array of same model objects. Results from fetchMany() will be stored here
     * @param array $data, optional array to set collection
     * @return $this|array
     */
    public function collection($data = null)
    {
        // If an array was passed, set collection to passed array
        if (is_array($data)) {
            $this->_collection = $data;

            return $this;
        }

        return (is_array($this->_collection)) ? $this->_collection : array();
    }

    /**
     * Return the collection array of same model objects as arrays.
     * @param array $data, optional array to set collection
     * @return $this|array
     */
    public function collectionToArray($data = null)
    {
        // If an array was passed, set collection to passed array
        if (is_array($data)) { $this->_collection = $data; }

        $collection = array();
        foreach ($this->_collection as $item) {
            $collection[] = $item->toArray();
        }

        return $collection;
    }

    /**
     * Return value of collection index if it exists
     * @param  int        $index
     * @return mixed|null
     */
    public function collectionIndex($index = 0)
    {
        $index = (int) $index;

        return (isset($this->_collection[$index])) ? $this->_collection[$index] : null;
    }

    /**
     * Delete this record from the database
     * @param int $primary
     * @return $this
     */
    public function delete($primary = null)
    {
        if (null !== $primary) {
            // Delete from table by passed primary key
            $query = join(' ', array(
                'DELETE FROM',
                $this->_table,
                'WHERE',
                $this->_primaryKey,
                '= ?',
            ));
            $params = array($primary);
        } elseif (count($this->_whereConditions) == 0) {
            // Delete from table by already set primary key
            $query = join(' ', array(
                'DELETE FROM',
                $this->_table,
                'WHERE',
                $this->_primaryKey,
                '= ?',
            ));
            $params = array($this->_data[$this->_primaryKey]);
        } else {
            // Delete from table based on built query
            $query = join(' ', array(
                'DELETE FROM',
                $this->_table,
                $this->statementConditions(),
                $this->statementLimit()
            ));
            $params = $this->_boundParams;
        }

        // Log the query
        $this->saveQueryLog($query, $params);

        try {
            // Prepare the statement
            $statement = $this->connection()->prepare($query);

            // Execute the prepared statement
            $statement->execute($params);
        } catch (Exception $e) {
            throw new \Exception(__LINE__ . ' ' . $e->getMessage() . "<br/>" . $this->lastQuery());
        }

        return $this;
    }

    /**
     * Save the model data to the database. Figure out if updating or inserting a new row
     */
    public function save()
    {
        // Get primary key
        $primary = $this->{$this->_primaryKey};

        if (null !== $primary && $primary > 0) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    /**
     * INSERT model data into new row
     */
    public function insert()
    {
        // Get primary key
        $primary = $this->{$this->_primaryKey};

        // Reset statement and bound parameters
        $this->_statement = '';
        $this->_boundParams = array();

        // Get all columns from defined fields
        $fields = array_flip(array_keys($this->_fields));

        // Get columns and set the value or use field default
        foreach ($fields as $key => $value) {
            if ($key != $this->_primaryKey) {
                $this->_queryColumns[] = $key;
                $this->_boundParams[] = (null === $this->$key) ? $this->_fields[$key]['default'] : $this->$key;
            }
        }

        $this->_statement = join(' ', array(
            'INSERT INTO',
            $this->_table,
            '(' . join(', ', $this->_queryColumns) . ')',
            'VALUES',
            '(' . join(', ', array_fill(0, count($this->_boundParams), '?')) . ')'
        ));

        $this->execute();

        // Update primary key
        if (null == $primary || $primary <= 0 || empty($primary)) {
            $this->_data[$this->_primaryKey] = $this->_conn->lastInsertId();
        }
    }

    /**
     * UPDATE model data into database
     */
    public function update()
    {
        // Reset statement and bound parameters
        $this->_statement = '';
        $this->_boundParams = array();

        // Get only updated fields
        $fields = array_intersect_key(array_merge($this->_data, $this->_dataModified), array_flip(array_keys($this->_fields)));

        // Get columns
        $sets = array();
        foreach ($fields as $key => $value) {
            if ($key != $this->_primaryKey) {
                $this->_queryColumns[] = $key;
                $this->_boundParams[] = (null === $this->$key) ? $this->_fields[$key]['default'] : $this->$key;
                $sets[] = $key . ' = ?';
            }
        }

        $this->_statement = join(' ', array(
            'UPDATE',
            $this->_table,
            'SET',
            join(', ', $sets),
        ));

        if (count($this->_whereConditions) == 0) {
            // Upsert where primary key matches
            $this->_statement .= ' ' . join(' ', array('WHERE', $this->_primaryKey, '= ?'));
            $this->_boundParams[] = $this->{$this->_primaryKey};
        } else {
            // Upsert table based on built query
            $this->_statement .= join(' ', array(
                $this->statementConditions(),
                $this->statementLimit()
            ));
        }

        $this->execute();
    }

    /**
     * INSERT/UPDATE model data into database
     */
    public function upsert()
    {
        // Reset statement and bound parameters
        $this->_statement = '';
        $this->_boundParams = array();

        // Get only updated fields
        $fields = array_intersect_key(array_merge($this->_data, $this->_dataModified), array_flip(array_keys($this->_fields)));

        // Get columns
        $sets = array();
        foreach ($fields as $key => $value) {
            $this->_queryColumns[] = $key;
            $this->_boundParams[] = (null === $this->$key) ? $this->_fields[$key]['default'] : $this->$key;
            $sets[] = $key . ' = ?';
        }

        $this->_statement = join(' ', array(
            'INSERT INTO',
            $this->_table,
            '(' . join(', ', $this->_queryColumns) . ')',
            'VALUES',
            '(' . join(', ', array_fill(0, count($this->_boundParams), '?')) . ')',
            'ON DUPLICATE KEY UPDATE',
            join(', ', $sets),
        ));

        // Re-add all parameters for the duplicate key update's values
        $this->_boundParams = array_merge($this->_boundParams, $this->_boundParams);

        if (count($this->_whereConditions) == 0) {
            // Upsert where primary key matches
            $this->_statement .= ' ' . join(' ', array('WHERE', $this->_primaryKey, '= ?'));
            $this->_boundParams[] = $this->{$this->_primaryKey};
        } else {
            // Upsert table based on built query
            $this->_statement .= join(' ', array(
                $this->statementConditions(),
                $this->statementLimit()
            ));
        }

        $this->execute();
    }

/**************************************************************************************/

    /**
     * Sets a field's value if the field is defined. Also allows for custom format or error checking
     * @param  $key
     * @param  $value
     * @return void
     * @throws FieldsAbstract\Exception
     */
    public function __setOld($key, $value)
    {
        // If attempting to set a property that doesnt exist throw exception
        if (!array_key_exists($key, $this->_data)) {
            throw new \Exception(__LINE__ . ' ' . __METHOD__ . '->' . $key . ' is not a valid property');
        }

        // If no filter method is set, just set the data key, set the field dirty and return
        if (!isset($this->_filters[$key])) {
            $this->_data[$key] = $value;
            $this->_setDirty($key);

            return;
        }

        // If filters doesnt begin with an underscore then assume we are running a static call
        if ($this->_filters[$key] !== true && preg_match('/^_/', $this->_filters[$key]) == false) {
            $this->_data[$key] = call_user_func($this->_filters[$key], $value);
            $this->_setDirty($key);

            return;
        }

        // Define the key's set method (if it exists)
        $setKeyMethod = '_set' . ucfirst($key);

        // If filters was just set to true, search for a _setFilterMethod
        if ($this->_filters[$key] === true && method_exists($this, $setKeyMethod)) {
            // If the filter is set and the key is set to (bool) true and the method exists, call it
            $this->{$setKeyMethod}($value);
            $this->_setDirty($key);

            return;
        }

        // Just call filter if it exists
        if (method_exists($this, $this->_filters[$key])) {
            // If the filter is set but the key holds a value other than true, try to call that method
            $this->{$this->_filters[$key]}($key, $value);
            $this->_setDirty($key);

            return;
        }
    }

/* INITIAL CACHE IMPLMENTATION */

    /**
     * Set cache module
     * @param Cache $cache
     */
    public function setCache($cache = null)
    {
        if (null !== $cache) { $this->_cache = $cache; }

        return $this;
    }

    /**
     * Read config from cache
     * @param  string     $key, key name to use in combination with cache prefix
     * @return bool|mixed
     * @todo $file undefined
     */
    protected function _readCache($key)
    {
        return false;

        // If no cache module return false
        if (is_null($this->_cache)) { return false; }

        // Read from cache
        $data = $this->_cache->get($this->_cachePrefix . '_' . md5($key));

        // Check if cache is valid
        if ($data === false || $data['fmt'] < filemtime($file)) {
            return false;
        }

        return $data['contents'];
    }

    /**
     * Write data to cache
     * @param  string $key, key name to use in combination with cache prefix
     * @return void
     * @todo $file undefined
     */
    protected function _writeCache($key)
    {
        return false;

        // If no cache module return false
        if (is_null($this->_cache)) { return false; }

        $file = Yaf_Application::app()->getConfig()->application->cacheDir . '/' . $this->_cachePrefix . '_' . md5($this->_cachePrefix);
        $fmt = (file_exists($file)) ? filemtime($file) : time();

        // Write data to cache
        $this->_cache->set(
            $this->_cachePrefix . '_' . md5($key),
            array(
                'fmt' => $fmt,
                'contents' => $this->collectionToArray(),
            ),
            2,
            $this->_cacheLifetime
        );
    }
}
