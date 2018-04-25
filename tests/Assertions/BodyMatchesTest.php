<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Messages\Transaction;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class BodyMatchesTest extends TestCase
{

    /** @test */
    public function itFailsIfTheExpectedBodyDoesNotMatchTheActualBody()
    {

        $expected = (new Transaction)->setRequest(
            (new RequestBuilder)->setBody('test body')->build()
        );
        $actual = (new Transaction)->setRequest(new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody('foo')
                ->build()
        ));

        $this->expectException(ExpectationFailedException::class);
        (new BodyMatches)->assert($actual, $expected);
    }

    /** @test */
    public function itDoesNotFailIfTheExpectedAndActualRequestBodiesMatch()
    {

        $expected = (new Transaction)->setRequest(
            (new RequestBuilder)->setBody('test body')->build()
        );
        $actual = (new Transaction)->setRequest(new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody('test body')
                ->build()
        ));

        (new BodyMatches)->assert($actual, $expected);
    }
}
