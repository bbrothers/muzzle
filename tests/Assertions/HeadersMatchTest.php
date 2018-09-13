<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class HeadersMatchTest extends TestCase
{

    /** @test */
    public function itFailsIfTheExpectedHeadersAreNotFoundInTheActualHeaders()
    {

        $expectation = new HeadersMatch(['Content-Type' => 'application/json']);
        $actual = new AssertableRequest(
            (new RequestBuilder(HttpMethod::POST()))
                ->setHeaders(['foo' => 'bar'])
                ->build()
        );

        $this->expectException(ExpectationFailedException::class);
        $expectation($actual, new Muzzle);
    }

    /** @test */
    public function itWillNotFailIfTheActualHeadersContainTheExpectedHeaders()
    {

        $expectation = new HeadersMatch(['Content-Type' => 'application/json']);

        $actual = new AssertableRequest(
            (new RequestBuilder)
                ->setHeaders(['foo' => 'bar', 'Content-Type' => 'application/json'])
                ->build()
        );

        $expectation($actual, new Muzzle);
    }

    /** @test */
    public function itWillMatchOnlyTheHeaderKeyIfAKeyIfTheProvidedValueIsNotAKeyValuePair()
    {

        $expectation = new HeadersMatch([
            'Content-Type' => 'application/json',
            'foo',
        ]);

        $assertable = new AssertableRequest(
            (new RequestBuilder)
                ->setHeaders(['foo' => 'bar', 'Content-Type' => 'application/json'])
                ->build()
        );

        $expectation($assertable, new Muzzle);

        $this->expectException(ExpectationFailedException::class);
        $expectation($assertable->withoutHeader('foo'), new Muzzle);
    }
}
