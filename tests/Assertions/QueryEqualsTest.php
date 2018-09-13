<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class QueryEqualsTest extends TestCase
{

    /** @test */
    public function itAssertsThatAQueryIsAnExactMatchToTheProvidedValues()
    {

        $expectation = new QueryEquals(['foo' => 'bar']);
        $assertable = new AssertableRequest(
            (new RequestBuilder(HttpMethod::POST()))
                ->setQuery(['foo' => 'bar'])
                ->build()
        );

        $expectation($assertable, new Muzzle);
        $this->expectException(ExpectationFailedException::class);
        $expectation(
            $assertable->withUri($assertable->getUri()->withQuery(http_build_query(
                ['foo' => 'bar', 'baz' => 'qux']
            ))),
            new Muzzle
        );
    }
}
