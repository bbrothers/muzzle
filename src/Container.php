<?php

namespace Muzzle;

use Countable;

class Container implements Countable
{

    /**
     * @var Muzzle[]
     */
    private $container = [];

    public function push(Muzzle $muzzle) : void
    {

        $this->container[] = $muzzle;
    }

    public function makeAssertions() : void
    {

        while (count($this->container)) {
            $muzzle = array_pop($this->container);
            $muzzle->makeAssertions();
            unset($muzzle);
        }
    }

    public function flush() : void
    {

        $this->container = [];
    }

    public function count() : int
    {

        return count($this->container);
    }
}
