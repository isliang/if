<?php
namespace Ipf\Utils;

trait TSingleton
{
    protected function __construct()
    {

    }
    protected function __clone()
    {

    }

    public static function getInstance()
    {
        static $instance = null;
        if (!$instance) {
            $ref = new \ReflectionClass(get_called_class());
            $ctor = $ref->getConstructor();
            $instance = $ref->newInstanceWithoutConstructor();
            $ctor->setAccessible(true);
            $ctor->invokeArgs($instance, func_get_args());
        }
        return $instance;
    }
}