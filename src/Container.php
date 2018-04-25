<?php

namespace Muzzle;

class Container
{

    /**
     * @var Muzzle[]
     */
    protected static $container = [];

    public static function push(Muzzle $muzzle) : void
    {

        static::$container[] = $muzzle;
    }

    public static function makeAssertions() : void
    {

        while(count(static::$container)) {
            $muzzle = array_pop(static::$container);
            $muzzle->makeAssertions();
            unset($muzzle);
        }
    }

    public static function flush() : void
    {

        static::$container = [];
    }
}
