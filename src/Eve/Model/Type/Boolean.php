<?php
namespace Eve\Model\Type;
use Eve\Model;

class Boolean implements TypeInterface
{
    /**
     * Cast given value to type required
     */
    public static function cast($value)
    {
        return (bool) $value;
    }

    /**
     * Geting value off Entity object
     */
    public static function get(Entity $entity, $value)
    {
        return self::cast($value);
    }

    /**
     * Setting value on Entity object
     */
    public static function set(Entity $entity, $value)
    {
        return self::cast($value);
    }
}
