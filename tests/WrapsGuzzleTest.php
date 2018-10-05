<?php

namespace Muzzle;

use BadMethodCallException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class WrapsGuzzleTest extends TestCase
{

    /** @test */
    public function itDelegatesSendToTheWrappedClient()
    {

        $client = $this->prophesize(ClientInterface::class);
        $request = new Request(HttpMethod::GET, '/');
        $response = new Response;
        $client->send($request, [])->willReturn($response);

        $wrapped = $this->wrap($client->reveal());
        $this->assertEquals($response, $wrapped->send($request, []));
    }

    /** @test */
    public function itDelegatesSendAsyncToTheWrappedClient()
    {

        $client = $this->prophesize(ClientInterface::class);
        $request = new Request(HttpMethod::GET, '/');
        $promise = new Promise;
        $client->sendAsync($request, [])->willReturn($promise);

        $wrapped = $this->wrap($client->reveal());
        $this->assertEquals($promise, $wrapped->sendAsync($request, []));
    }

    /** @test */
    public function itDelegatesRequestToTheWrappedClient()
    {

        $client = $this->prophesize(ClientInterface::class);
        $request = [HttpMethod::GET, '/', []];
        $response = new Response;
        $client->request(...$request)->willReturn($response);

        $wrapped = $this->wrap($client->reveal());
        $this->assertEquals($response, $wrapped->request(...$request));
    }

    /** @test */
    public function itDelegatesRequestAsyncToTheWrappedClient()
    {

        $client = $this->prophesize(ClientInterface::class);
        $request = [HttpMethod::GET, '/', []];
        $promise = new Promise;
        $client->requestAsync(...$request)->willReturn($promise);

        $wrapped = $this->wrap($client->reveal());
        $this->assertEquals($promise, $wrapped->requestAsync(...$request));
    }

    /** @test */
    public function itDelegatesGetConfigToTheWrappedClient()
    {

        $client = $this->prophesize(ClientInterface::class);
        $option = 'foo';
        $client->getConfig($option)->willReturn($option);

        $wrapped = $this->wrap($client->reveal());
        $this->assertEquals($option, $wrapped->getConfig($option));
    }

    /** @test */
    public function itThrowsABadMethodCallExceptionForUnhandledMethods()
    {

        $wrapped = $this->wrap(new Client);
        $this->expectException(InvalidArgumentException::class);
        $wrapped->unDefinedMethod();
    }

    private function wrap(ClientInterface $client) : ClientInterface
    {

        return new class($client) implements ClientInterface
        {

            use WrapsGuzzle;

            private $client;

            public function __construct(ClientInterface $client)
            {

                $this->client = $client;
            }
        };
    }
}
