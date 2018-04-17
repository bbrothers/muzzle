<?php

namespace Muzzle\Middleware;

use Muzzle\Messages\DecodableResponse;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class DecodableTest extends TestCase
{

    /** @test */
    public function itReturnsADecodableResponse()
    {

        $stack = new HandlerStack(new MockHandler([
            new Response(200, [], json_encode(['foo' => 'bar'])),
        ]));
        $stack->push(new Decodable);
        $handler = $stack->resolve();

        $response = $handler(new Request('GET', '/test'))->wait();

        $this->assertInstanceOf(DecodableResponse::class, $response);
        $this->assertTrue($response->isJson());
        $this->assertEquals(['foo' => 'bar'], $response->decode());
    }
}
