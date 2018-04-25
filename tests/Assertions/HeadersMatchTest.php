<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Messages\Transaction;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class HeadersMatchTest extends TestCase
{

    /** @test */
    public function itFailsIfTheExpectedHeadersAreNotFoundInTheActualHeaders()
    {

        $expected = (new Transaction)->setRequest(
            (new RequestBuilder)
                ->setHeaders(['Content-Type' => 'application/json'])
                ->build()
        );
        $actual = (new Transaction)->setRequest(new AssertableRequest(
            (new RequestBuilder(HttpMethod::POST()))
                ->setHeaders(['foo' => 'bar'])
                ->build()
        ));

        $this->expectException(ExpectationFailedException::class);
        (new HeadersMatch)->assert($actual, $expected);
    }

    /** @test */
    public function itWillNotFailIfTheActualHeadersContainTheExpectedHeaders()
    {

        $expected = (new Transaction)->setRequest(
            (new RequestBuilder)
                ->setHeaders(['Content-Type' => 'application/json'])
                ->build()
        );
        $actual = (new Transaction)->setRequest(new AssertableRequest(
            (new RequestBuilder)
                ->setHeaders(['foo' => 'bar', 'Content-Type' => 'application/json'])
                ->build()
        ));

        (new HeadersMatch)->assert($actual, $expected);
    }
}
