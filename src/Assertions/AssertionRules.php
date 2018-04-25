<?php

namespace Muzzle\Assertions;

use MultipleIterator;
use Muzzle\Messages\Transaction;
use Muzzle\Muzzle;

class AssertionRules
{

    protected static $assertions = [
        ExpectedRequestWasMade::class,
        UriPathMatches::class,
        MethodMatches::class,
        QueryContains::class,
        BodyMatches::class,
        HeadersMatch::class,
    ];

    /**
     * @var Muzzle
     */
    private $muzzle;

    public function __construct(Muzzle $muzzle)
    {

        $this->muzzle = $muzzle;
    }

    public static function new(Muzzle $muzzle) : self
    {

        return new self($muzzle);
    }

    public function runAssertions() : void
    {

        $assertions = $this->build();
        /**
         * @var Transaction $actual
         * @var Transaction $expected
         */
        foreach ($this->iterator() as [$actual, $expected]) {
            foreach ($assertions as $assertion) {
                $assertion->assert($actual, $expected);
            }
        }
    }

    public static function push(string $assertion) : void
    {

        static::assertAssertionClass($assertion);
        static::$assertions[] = $assertion;
    }

    public static function unshift(string $assertion) : void
    {

        static::assertAssertionClass($assertion);
        array_unshift(static::$assertions, $assertion);
    }

    /**
     * @return Assertion[]
     */
    public static function assertions() : array
    {

        return static::$assertions;
    }

    public static function setAssertions(string ...$assertions) : void
    {

        foreach ($assertions as $assertion) {
            static::assertAssertionClass($assertion);
        }

        static::$assertions = $assertions;
    }

    /**
     * @return Assertion[]
     */
    private function build() : array
    {

        return array_map(function ($assertion) {

            return new $assertion($this->muzzle);
        }, static::$assertions);
    }

    private function iterator() : MultipleIterator
    {

        $iterator = new MultipleIterator(MultipleIterator::MIT_NEED_ANY);
        $iterator->attachIterator($this->muzzle->history()->getIterator());
        $iterator->attachIterator($this->muzzle->expectations()->getIterator());
        return $iterator;
    }

    private static function assertAssertionClass(string $class) : void
    {

        if (! in_array(Assertion::class, class_implements($class))) {
            throw new \InvalidArgumentException('Must be an Assertion.');
        }
    }
}
