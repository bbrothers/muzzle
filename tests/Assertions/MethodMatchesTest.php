<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class MethodMatchesTest extends TestCase
{

    /** @test */
    public function itFailsTheTestIfTheExpectedAndActualMethodsDoNotMatch()
    {

        $request = (new RequestBuilder)->setMethod(HttpMethod::POST())->build();
        $request = AssertableRequest::fromBaseRequest($request);

        $this->expectException(ExpectationFailedException::class);
        (new MethodMatches(HttpMethod::GET, HttpMethod::PUT))($request, new Muzzle);
    }

    /** @test */
    public function itWillNotFailIfTheExpectedAndActualMethodsMatch()
    {

        $request = (new RequestBuilder)->setMethod(HttpMethod::POST())->build();
        $request = AssertableRequest::fromBaseRequest($request);

        (new MethodMatches(HttpMethod::POST, HttpMethod::PUT))($request, new Muzzle);
    }
}
