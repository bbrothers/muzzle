<?php

namespace Muzzle;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestBuilderTest extends TestCase
{

    /** @test */
    public function itCanConstructARequest()
    {

        $builder = new RequestBuilder;

        $request = $builder
            ->setMethod(HttpMethod::GET())
            ->setUri('http://example.com')
            ->setQuery(['foo' => 'bar'])
            ->setHeaders(['Accept' => 'application/json'])
            ->build();

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertEquals('http://example.com?foo=bar', $request->getUri());
    }

    /** @test */
    public function itCanConstructARequestUriWithNestedQueryParameters()
    {

        $builder = new RequestBuilder;

        $request = $builder
            ->setMethod(HttpMethod::GET())
            ->setUri('http://example.com')
            ->setQuery(['foo' => [['bar' => ['baz', 'qux']]]])
            ->build();

        $this->assertEquals(
            'http://example.com?foo[0][bar][0]=baz&foo[0][bar][1]=qux',
            urldecode($request->getUri())
        );
    }

    /** @test */
    public function itHasAHelperToSetAnArrayAsJsonOnTheBodyOfTheRequest()
    {

        $builder = new RequestBuilder;

        $body = ['foo' => [['bar' => ['baz', 'qux']]]];
        $request = $builder
            ->setMethod(HttpMethod::POST())
            ->setUri('http://example.com')
            ->setJson($body)
            ->build();

        $this->assertEquals(
            json_encode($body),
            (string) $request->getBody()
        );
    }
}
