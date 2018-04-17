<?php

namespace Middleware;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Muzzle\HttpStatus;
use Muzzle\Middleware\History;
use Muzzle\Transactions;
use PHPUnit\Framework\TestCase;

class HistoryTest extends TestCase
{

    /** @test */
    public function itTracksASuccessfulTransaction()
    {

        $stack = new HandlerStack(new MockHandler([new Response(HttpStatus::OK)]));
        $history = new Transactions;
        $options = ['base_uri' => 'https://example.com'];
        $stack->push(new History($history));
        $request = new Request('GET', '/test');
        $handler = $stack->resolve();
        $handler($request, $options)->wait();

        $this->assertInstanceOf(\Muzzle\Messages\Transaction::class, $history->last());
        $history->last()->response()->assertSuccessful();
        $history->last()->request()->assertMethod('GET')->assertUriPath('/test');
        $this->assertSame($options, $history->last()->options());
    }

    /** @test */
    public function itTracksAFailedRequest()
    {

        $request = new Request('GET', '/test');
        $stack = new HandlerStack(new MockHandler([
            new RequestException('Failed Request', $request),
        ]));
        $history = new Transactions;
        $stack->push(new History($history));
        $handler = $stack->resolve();
        $handler($request, [RequestOptions::HTTP_ERRORS => false])
            ->otherwise(function ($reason) use ($history) {

                $this->assertEmpty($history->last()->response());
                $history->last()->request()->assertUriPath('/test');
                $this->assertSame($reason, $history->last()->error());
            })
            ->wait();
    }
}
