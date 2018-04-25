<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\Transaction;
use PHPUnit\Framework\Assert as PHPUnit;

class ExpectedRequestWasMade implements Assertion
{

    public function assert(?Transaction $actual, Transaction $expected) : void
    {

        PHPUnit::assertInstanceOf(Transaction::class, $actual, sprintf(
            'The expected request [%s]%s was not made.',
            $expected->request()->getMethod(),
            $expected->request()->getUri()->__toString()
        ));
    }
}
