<?php
namespace Eve\Model;

/**
* Entity object
*
* @package Eve\Model
*/
abstract class Entity
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

    // Entity data storage
    protected $data = array();
    protected $dataModified = array();

    // Entity error messages (may be present after save attempt)
    protected $errors = array();

    // Query object
    protected $query;

    /**
     * Constructor - allows setting of object properties with array on construct
     *
     * @param array $data, populate entity from array of data
     */
    public function __construct(array $data = array())
    {
        $this->initFields();

        // Set given data
        if ($data) {
            $this->data($data, false);
        }
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
}
