<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class QueryContainsTest extends TestCase
{

    /** @test */
    public function itFailsIfTheExpectedQueryParametersAreNotFoundInTheActualQueryParameters()
    {

        $expectation = new QueryContains(['foo' => 'bar']);
        $actual = new AssertableRequest(
            (new RequestBuilder(HttpMethod::POST()))
                ->setQuery(['foo' => 'baz'])
                ->build()
        );

        $this->expectException(ExpectationFailedException::class);
        $expectation($actual, new Muzzle);
    }

    /** @test */
    public function itWillNotFailIfTheExpectedQueryParametersAreFoundInTheActualQueryParameters()
    {

        $expectation = new QueryContains(['foo' => 'bar']);
        $actual = new AssertableRequest(
            (new RequestBuilder)
                ->setQuery(['foo' => 'bar', 'baz' => 'qux'])
                ->build()
        );

        $expectation($actual, new Muzzle);
    }

    /** @test */
    public function itCanMatchRegexQueryValues()
    {

        $expectation = new QueryContains(['data' => [['foo' => ['bar' => '#b.*z#']]]]);
        $actual = new AssertableRequest(
            (new RequestBuilder)
                ->setQuery(['data' => [['foo' => ['bar' => 'baz']]]])
                ->build()
        );

        $expectation($actual, new Muzzle);
        $expectation(
            $actual->withUri($actual->getUri()->withQuery(http_build_query(
                ['data' => [['foo' => ['bar' => 'buzz']]]]
            ))),
            new Muzzle
        );
        $this->expectException(ExpectationFailedException::class);
        $expectation(
            $actual->withUri($actual->getUri()->withQuery(http_build_query(
                ['data' => [['foo' => ['bar' => 'qux']]]]
            ))),
            new Muzzle
        );
    }
}
