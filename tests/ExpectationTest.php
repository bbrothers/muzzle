<?php

namespace Muzzle;

use Muzzle\Assertions\MethodMatches;
use Muzzle\Assertions\UriPathMatches;
use PHPUnit\Framework\TestCase;

class ExpectationTest extends TestCase
{

    /** @test */
    public function itCanBeInstantiatedWithArguments()
    {

        $expectation = new Expectation(HttpMethod::GET, '/');

        $this->assertEquals(
            new MethodMatches(HttpMethod::GET),
            $expectation->assertion('method')
        );

        $this->assertEquals(
            new UriPathMatches('/'),
            $expectation->assertion('uri')
        );
    }

    /** @test */
    public function itIgnoresEmptyValuesOnAssertionMethods()
    {

        $methods = ['method', 'uri', 'headers', 'query', 'queryShouldEqual', 'body', 'bodyShouldEqual', 'json'];
        $expectation = new Expectation;

        foreach ($methods as $method) {
            $expectation->{$method}(null);
        }

        $this->assertEmpty($expectation->assertions());
    }

    /** @test */
    public function itWillThrowAnErrorIfAnInvalidReplyValueIsGiven() : void
    {

        $this->expectException(InvalidResponseProvided::class);
        (new Expectation)->replyWith('invalid');
    }
}
