<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\Transaction;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class ExpectedRequestWasMadeTest extends TestCase
{

    /** @test */
    public function itRaisesAnAssertionExceptionIfAnExpectedHttpCallIsNotMade()
    {

        $expectation = (new Transaction)->setRequest((new RequestBuilder)->build());

        $this->expectException(ExpectationFailedException::class);
        (new ExpectedRequestWasMade)->assert(null, $expectation);
    }

    /** @test */
    public function itWillNotFailIfARequestWasMade()
    {

        $expectation = (new Transaction)->setRequest((new RequestBuilder)->build());
        $actual = (new Transaction)->setRequest((new RequestBuilder)->build());

        (new ExpectedRequestWasMade)->assert($actual, $expectation);
    }
}
