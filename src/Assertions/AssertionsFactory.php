<?php

namespace Muzzle\Assertions;

use Generator;
use MultipleIterator;
use Muzzle\Messages\Transaction;
use Muzzle\Transactions;

class AssertionsFactory
{

    protected static $assertions = [
        UriPathMatches::class,
        MethodMatches::class,
        QueryContains::class,
        BodyMatches::class,
        HeadersMatch::class,
    ];

    public static function new() : self
    {

        return new self;
    }

    public function runAssertions(Transactions $history, Transactions $expectations) : void
    {

        /**
         * @var Transaction $actual
         * @var Transaction $expected
         */
        foreach ($this->iterator($history, $expectations) as [$actual, $expected]) {
            foreach ($this->build() as $assertion) {
                $assertion->assert($actual, $expected);
            }
        }
    }

    /**
     * @return Generator|Assertion[]
     */
    public function build() : Generator
    {

        yield from array_map(function ($assertion) {
            return new $assertion;
        }, static::$assertions);
    }

    private function iterator(Transactions $history, Transactions $expectations) : MultipleIterator
    {

        $iterator = new MultipleIterator;
        $iterator->attachIterator($history->getIterator());
        $iterator->attachIterator($expectations->getIterator());
        return $iterator;
    }
}
