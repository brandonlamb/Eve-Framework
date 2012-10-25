<?php
namespace Eve\Model\Type;
use Eve\Model;

interface TypeInterface
{
    public static function cast($value);
    public static function get(Entity $entity, $name);
    public static function set(Entity $entity, $name);
}
