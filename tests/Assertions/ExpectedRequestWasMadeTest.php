<?php

namespace Muzzle\Assertions;

use Muzzle\Expectation;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class ExpectedRequestWasMadeTest extends TestCase
{

    /** @test */
    public function itRaisesAnAssertionExceptionIfAnExpectedHttpCallIsNotMade()
    {

        $expectation = new ExpectedRequestWasMade(new Expectation);

        $this->expectException(ExpectationFailedException::class);
        $expectation(null, new Muzzle);
    }

    /** @test */
    public function itWillNotFailIfARequestWasMade()
    {

        $expectation = new ExpectedRequestWasMade(new Expectation);

        $assertion = new AssertableRequest((new RequestBuilder)->build());

        $expectation($assertion, new Muzzle);
    }
}
