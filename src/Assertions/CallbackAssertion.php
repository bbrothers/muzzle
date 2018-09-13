<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;

class CallbackAssertion implements Assertion
{

    /**
     * @var callable
     */
    private $callable;

    public function __construct(callable $callable)
    {

        $this->callable = $callable;
    }

    public function __invoke(AssertableRequest $actual, Muzzle $muzzle) : void
    {

        ($this->callable)($actual, $muzzle);
    }
}
