<?php
/**
 * Eve Application Framework
 *
 * @author Brandon Lamb
 * @copyright 2012
 * @package Eve\Mvc
 * @version 0.1.0
 */
namespace Eve\Mvc;

abstract class Component
{
    /**
     * @var array, config
     */
    protected $config;

    /**
     * @var array, magic set/get data
     */
    protected $data;

    /**
     * Accepts a config array
     *
     * @param  array $config
     * @return void
     */
    public function __construct(array $config = array())
    {
        $this->config = $config;
        $this->data = array();
    }

    /**
     * Returns a property value, an event handler list or a behavior based on its name.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using the following syntax to read a property or obtain event handlers:
     * <pre>
     * $value=$component->propertyName;
     * $handlers=$component->eventName;
     * </pre>
     *
     * @param  string    $name the property name or event name
     * @return mixed     the property value, event handlers attached to the event, or the named behavior
     * @throws Exception if the property or event is not defined
     * @see __set
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (isset($this->data, $this->data[$name])) {
            return $this->data[$name];
        }
        throw new Exception('Property "' . get_class($this) . '.' . $name . '" is not defined.');
    }

    /**
     * Sets value of a component property.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using the following syntax to set a property or attach an event handler
     * <pre>
     * $this->propertyName=$value;
     * $this->eventName=$callback;
     * </pre>
     *
     * @param  string     $name  the property name or the event name
     * @param  mixed      $value the property value or callback
     * @return mixed
     * @throws CException if the property/event is not defined or the property is read only.
     * @see __get
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        } elseif (isset($this->data)) {
            $this->data[$name] = $value;

            return $this;
        }

        if (method_exists($this, 'get' . $name)) {
            throw new Exception('Property "' . get_class($this) . '.' . $name . '" is read only.');
        } else {
            throw new Exception('Property "' . get_class($this) . '.' . $name . '" is not defined.');
        }
    }

    /**
     * Checks if a property value is null.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using isset() to detect if a component property is set or not.
     *
     * @param  string  $name the property name or the event name
     * @return boolean
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }

        return false;
    }

    /**
     * Sets a component property to be null.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using unset() to set a component property to be null.
     *
     * @param  string     $name the property name or the event name
     * @throws CException if the property is read only.
     * @return mixed
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new Exception('Property "' . get_class($this) . '.' . $name . '" is read only.');
        }
    }

    /**
     * Determines whether a property is defined. A property is defined if there is a getter
     * or setter method defined in the class. Note, property names are case-insensitive.
     *
     * @param  string  $name the property name
     * @return boolean whether the property is defined
     * @see canGetProperty
     * @see canSetProperty
     */
    public function hasProperty($name)
    {
        return method_exists($this, 'get' . $name) || method_exists($this, 'set' . $name);
    }

    /**
     * Determines whether a property can be read. A property can be read if the class
     * has a getter method for the property name. Note, property name is case-insensitive.
     *
     * @param  string  $name the property name
     * @return boolean whether the property can be read
     * @see canSetProperty
     */
    public function canGetProperty($name)
    {
        return method_exists($this, 'get' . $name);
    }

    /**
     * Determines whether a property can be set.
     * A property can be written if the class has a setter method
     * for the property name. Note, property name is case-insensitive.
     * @param  string  $name the property name
     * @return boolean whether the property can be written
     * @see canGetProperty
     */
    public function canSetProperty($name)
    {
        return method_exists($this, 'set' . $name);
    }
}
