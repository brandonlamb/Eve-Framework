<?php
/**
 * Eve Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package Eve\App
 * @version 0.1.0
 */
namespace Eve\Mvc;

class Config implements \Countable
{
    /**
     * Config filename
     *
     * @var string
     */
    protected $_file;

    /**
     * Data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Set magic method
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Get magic method
     *
     * @param  string $name
     * @return mixed
     */
    public function &__get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        $default = null;

        return $default;
    }

    /**
     * Load configuration from file
     *
     * @param  string $filename
     * @param  string $section
     * @return void
     */
    public function __construct($file)
    {
        $this->_file = $file;
        if ($resourceAbsolutePath = stream_resolve_include_path($file)) {
            $this->_data = require $resourceAbsolutePath;
        } else {
            throw new \Exception('Config file does not exist (' . $file . ')');
        }
    }

    /**
     * Set value method
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function set($name, $value)
    {
        $this->_data[$name] =& $value;
    }

    /**
     * Get value method
     *
     * @param  string $name
     * @return mixed
     */
    public function &get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        $default = null;

        return $default;
    }

    /**
     * Isset magic method
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * Get count of items
     *
     * @return int
     */
    public function count()
    {
        return count($this->_data);
    }

    /**
     * Merges two or more arrays into one recursively. If each array has an element with the same string key value,
     * the latter will overwrite the former (different from array_merge_recursive). Recursive merging will be conducted
     * if both arrays have an element of array type and are having the same key. For integer-keyed elements,
     * the elements from the latter array will be appended to the former array.
     *
     * First parameter must be array to be merged to, second and more parameters should be arrays to be merged from.
     * You can specifiy additional arrays via 3rd, 4th argument, etc.
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function mergeArray()
    {
        $args = func_get_args();
        $res = array_shift($args);

        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_integer($k)) {
                    isset($res[$k]) ? $res[] = $v : $res[$k] = $v;
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = static::mergeArray($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }
}
