<?php
namespace Eve\Model\Type;
use Eve\Model;

class Date implements TypeInterface
{
    /**
     * Cast given value to type required
     */
    public static function cast($value)
    {
        if (is_string($value) || is_numeric($value)) {
            // Create new \DateTime instance from string value
            if (is_numeric($value)) {
              $value = new \DateTime('@' . $value);
            } elseif ($value) {
              $value = new \DateTime($value);
            } else {
              $value = null;
            }
        }

        return $value;
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
