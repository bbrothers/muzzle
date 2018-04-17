<?php

namespace Muzzle;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Illuminate\Support\Arr;
use IteratorAggregate;
use Muzzle\Messages\Transaction;

class Transactions implements ArrayAccess, IteratorAggregate, Countable
{

    /**
     * @var Transaction[]
     */
    private $transactions;

    public function __construct(array $transactions = [])
    {

        $this->transactions = $transactions;
    }


    /**
     * Push an item onto the end of the collection.
     *
     * @param  mixed $value
     * @return $this
     */
    public function push($value) : Transactions
    {

        $this->offsetSet(null, $value);

        return $this;
    }

    /**
     * Get the last item from the collection.
     *
     * @param  callable|null $callback
     * @param  mixed $default
     * @return Transaction|mixed
     */
    public function last(callable $callback = null, $default = null)
    {

        return Arr::last($this->transactions, $callback, $default);
    }

    /**
     * Get the first item from the collection.
     *
     * @param  callable|null $callback
     * @param  mixed $default
     * @return Transaction|mixed
     */
    public function first(callable $callback = null, $default = null)
    {

        return Arr::first($this->transactions, $callback, $default);
    }

    public function transactions() : array
    {

        return $this->transactions;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() : ArrayIterator
    {

        return new ArrayIterator($this->transactions);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset) : bool
    {

        return isset($this->transactions[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {

        return $this->transactions[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value) : self
    {

        if ($offset === null) {
            $this->transactions[] = $value;
        } else {
            $this->transactions[$offset] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset) : void
    {

        unset($this->transactions[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {

        return count($this->transactions);
    }
}
