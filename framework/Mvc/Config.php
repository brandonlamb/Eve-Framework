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
    protected $file;

    /**
     * Data
     *
     * @var array
     */
    protected $data;

    /**
     * Load configuration from file
     *
     * @param  string $filename
     * @param  string $section
     * @return void
     */
    public function __construct($file = null)
    {
        if ($resourceAbsolutePath = stream_resolve_include_path($file)) {
            $this->file = $file;
            $this->data = require $resourceAbsolutePath;
        } else {
            $this->data = array();
        }
    }

    /**
     * Set magic method
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Get magic method
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Set value method
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Config
     */
    public function set($name, $value)
    {
        $this->data[$name] =& $value;
        return $this;
    }

    /**
     * Get value method
     *
     * @param  string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    /**
     * Isset magic method
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Get count of items
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Set the configuration data array
     *
     * @param array $data
     * @return Config
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
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
