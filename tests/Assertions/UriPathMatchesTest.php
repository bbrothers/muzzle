<?php

namespace Muzzle\Assertions;

use GuzzleHttp\Psr7\Uri;
use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Messages\Transaction;
use Muzzle\Muzzle;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class UriPathMatchesTest extends TestCase
{

    /** @test */
    public function itFailsIfAGivenUriWithTheConfiguredDefaultPathDoesNotMatchTheExpectedPath()
    {

        $muzzle = $this->prophesize(Muzzle::class);
        $muzzle->getConfig('base_uri')
               ->willReturn(new Uri('https://example.com/api'));

        $assertion = new UriPathMatches($muzzle->reveal());

        $expectedUri = (new Transaction)->setRequest(
            (new RequestBuilder(HttpMethod::GET(), '/foo/bar'))->build()
        );
        $actualUri = (new Transaction)->setRequest(
            new AssertableRequest(
                (new RequestBuilder(HttpMethod::GET(), '/api/foo/bar'))->build()
            )
        );

        $this->expectException(ExpectationFailedException::class);
        $assertion->assert($actualUri, $expectedUri);
    }

    /** @test */
    public function itMatchesAGivenUriWithTheConfiguredDefaultPath()
    {

        $muzzle = $this->prophesize(Muzzle::class);
        $muzzle->getConfig('base_uri')
               ->willReturn(new Uri('https://example.com/api'));

        $assertion = new UriPathMatches($muzzle->reveal());

        $expectedUri = (new Transaction)->setRequest(
            (new RequestBuilder(HttpMethod::GET(), '/foo/bar'))->build()
        );
        $actualUri = (new Transaction)->setRequest(
            new AssertableRequest(
                (new RequestBuilder(HttpMethod::GET(), '/foo/bar'))->build()
            )
        );

        $assertion->assert($actualUri, $expectedUri);
    }
}
