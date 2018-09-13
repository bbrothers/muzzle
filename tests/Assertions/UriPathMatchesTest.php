<?php

namespace Muzzle\Assertions;

use GuzzleHttp\Psr7\Uri;
use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
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

        $assertion = new UriPathMatches('/foo/bar');

        $actual = AssertableRequest::fromBaseRequest(
            (new RequestBuilder(HttpMethod::GET(), '/api/foo/bar'))->build()
        );

        $this->expectException(ExpectationFailedException::class);
        $assertion($actual, $muzzle->reveal());
    }

    /** @test */
    public function itMatchesAGivenUriWithTheConfiguredDefaultPath()
    {

        $muzzle = $this->prophesize(Muzzle::class);
        $muzzle->getConfig('base_uri')
               ->willReturn(new Uri('https://example.com/api'));

        $assertion = new UriPathMatches('/foo/bar');

        $actual = AssertableRequest::fromBaseRequest(
            (new RequestBuilder(HttpMethod::GET(), '/foo/bar'))->build()
        );

        $assertion($actual, $muzzle->reveal());
    }


    /** @test */
    public function itAllowsMatchingWildcardParameters()
    {

        $muzzle = $this->prophesize(Muzzle::class);
        $muzzle->getConfig('base_uri')
               ->willReturn(new Uri('https://example.com/api'));

        $assertion = new UriPathMatches('/foo/*/bar');

        $actual = AssertableRequest::fromBaseRequest(
            (new RequestBuilder(HttpMethod::GET(), '/foo/123/bar'))->build()
        );

        $assertion($actual, $muzzle->reveal());
    }

    /** @test */
    public function itCanMatchARegexPattern()
    {

        $muzzle = $this->prophesize(Muzzle::class);
        $muzzle->getConfig('base_uri')
               ->willReturn(new Uri('https://example.com/api'));

        $assertion = new UriPathMatches('#foo\/\d+\/[bar|baz]#');

        $actual = AssertableRequest::fromBaseRequest(
            (new RequestBuilder(HttpMethod::GET(), '/foo/123/bar'))->build()
        );

        $assertion($actual, $muzzle->reveal());

        $actual = AssertableRequest::fromBaseRequest(
            (new RequestBuilder(HttpMethod::GET(), '/foo/22/baz'))->build()
        );

        $assertion($actual, $muzzle->reveal());
    }
}
