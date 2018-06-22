<?php

namespace Muzzle;

use ArrayAccess;
use Countable;
use Illuminate\Support\Arr;
use IteratorAggregate;
use Muzzle\Messages\Transaction;

class Transactions implements ArrayAccess, IteratorAggregate, Countable
{

    use ArrayAccessible;

    public function __construct(array $transactions = [])
    {

        foreach ($transactions as $transaction) {
            if (! $transaction instanceof Transaction) {
                throw NotATransaction::value($transaction);
            }
        }

        $this->items = $transactions;
    }

    public function push(Transaction $value) : Transactions
    {

        $this->offsetSet(null, $value);

        return $this;
    }

    public function prepend($value, $key = null) : Transactions
    {

        $this->items = Arr::prepend($this->items, $value, $key);

        return $this;
    }

    public function last(callable $callback = null) : ?Transaction
    {

        return Arr::last($this->items, $callback);
    }

    public function first(callable $callback = null) : ?Transaction
    {

        return Arr::first($this->items, $callback);
    }

    public function map(callable $callback) : Transactions
    {

        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    public function filter(callable $callback) : Transactions
    {

        return new static(Arr::where($this->items, $callback));
    }

    public function get($key) : ?Transaction
    {

        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }

        return null;
    }

    public function has(...$keys) : bool
    {

        foreach ($keys as $value) {
            if (! $this->offsetExists($value)) {
                return false;
            }
        }

        return true;
    }

    public function isEmpty() : bool
    {

        return empty($this->items);
    }

    public function isNotEmpty() : bool
    {

        return ! $this->isEmpty();
    }

    public function transactions() : array
    {

        return $this->items;
    }
}
