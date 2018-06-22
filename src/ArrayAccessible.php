<?php

namespace Muzzle;

use ArrayIterator;

trait ArrayAccessible
{

    protected $items = [];

    /**
     * {@inheritdoc}
     */
    public function getIterator() : ArrayIterator
    {

        return new ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset) : bool
    {

        return isset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {

        return $this->items[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value) : self
    {

        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset) : void
    {

        unset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {

        return count($this->items);
    }
}
