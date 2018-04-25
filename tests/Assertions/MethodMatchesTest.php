<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Messages\Transaction;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class MethodMatchesTest extends TestCase
{

    /** @test */
    public function itFailsTheTestIfTheExpectedAndActualMethodsDoNotMatch()
    {

        $expected = (new Transaction)->setRequest((new RequestBuilder)->build());
        $actual = (new Transaction)->setRequest(
            new AssertableRequest((new RequestBuilder(HttpMethod::POST()))->build())
        );

        $this->expectException(ExpectationFailedException::class);
        (new MethodMatches)->assert($actual, $expected);
    }

    /** @test */
    public function itWillNotFailIfTheExpectedAndActualMethodsMatch()
    {

        $expected = (new Transaction)->setRequest((new RequestBuilder)->build());
        $actual = (new Transaction)->setRequest(
            new AssertableRequest((new RequestBuilder)->build())
        );

        (new MethodMatches)->assert($actual, $expected);
    }
}
