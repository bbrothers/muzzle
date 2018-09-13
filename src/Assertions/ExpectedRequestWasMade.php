<?php

namespace Muzzle\Assertions;

use Muzzle\Expectation;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\Http\Message\RequestInterface;

class ExpectedRequestWasMade implements Assertion
{

    private $expected;

    public function __construct(Expectation $expected)
    {

        $this->expected = $expected;
    }

    public function __invoke(?AssertableRequest $actual, Muzzle $muzzle) : void
    {

        PHPUnit::assertInstanceOf(RequestInterface::class, $actual, sprintf(
            'The expected request %s was not made.',
            (string) $this->expected
        ));
    }
}
