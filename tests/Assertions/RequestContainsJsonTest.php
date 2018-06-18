<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Messages\Transaction;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class RequestContainsJsonTest extends TestCase
{

    /** @test */
    public function itFailsIfTheActualRequestDoesNotContainTheExpectedJson()
    {

        $expected = (new Transaction)->setRequest(
            (new RequestBuilder)->setJson(['foo' => 'bar'])->build()
        );
        $actual = (new Transaction)->setRequest(new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody('test body')
                ->build()
        ));

        $this->expectException(ExpectationFailedException::class);
        (new RequestContainsJson)->assert($actual, $expected);
    }

    /** @test */
    public function itDoesNotFailIfTheActualRequestContainsTheExpectedJson()
    {

        $expected = (new Transaction)->setRequest(
            (new RequestBuilder)->setJson(['foo' => 'bar'])->build()
        );
        $actual = (new Transaction)->setRequest(new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setJson([
                    'foo' => 'bar',
                    'baz' => 'qux',
                ])
                ->build()
        ));

        (new RequestContainsJson)->assert($actual, $expected);
    }
}
