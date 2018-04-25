<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Messages\Transaction;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class QueryContainsTest extends TestCase
{

    /** @test */
    public function itFailsIfTheExpectedQueryParametersAreNotFoundInTheActualQueryParameters()
    {

        $expected = (new Transaction)->setRequest(
            (new RequestBuilder)->setQuery(['foo' => 'bar'])->build()
        );
        $actual = (new Transaction)->setRequest(new AssertableRequest(
            (new RequestBuilder(HttpMethod::POST()))
                ->setQuery(['foo' => 'baz'])
                ->build()
        ));

        $this->expectException(ExpectationFailedException::class);
        (new QueryContains)->assert($actual, $expected);
    }

    /** @test */
    public function itWillNotFailIfTheExpectedQueryParametersAreFoundInTheActualQueryParameters()
    {

        $expected = (new Transaction)->setRequest(
            (new RequestBuilder)->setQuery(['foo' => 'bar'])->build()
        );
        $actual = (new Transaction)->setRequest(new AssertableRequest(
            (new RequestBuilder)
                ->setQuery(['foo' => 'bar',  'baz' => 'qux'])
                ->build()
        ));

        (new QueryContains)->assert($actual, $expected);
    }
}
