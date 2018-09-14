<?php

namespace Muzzle;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Muzzle\Middleware\Decodable;
use PHPUnit\Framework\TestCase;

function app()
{

    return new class
    {

        function extend(string $class, callable $callable)
        {

            return $callable(new Client(['base_uri' => 'https://example.com']));
        }
    };
}

class MuzzleBuilderTest extends TestCase
{

    public function tearDown()
    {

        parent::tearDown();
        Muzzle::close();
    }

    /** @test */
    public function itCanBuildANewClientInstance()
    {

        $this->assertInstanceOf(Muzzle::class, MuzzleBuilder::create()->build());
    }

    /** @test */
    public function itCanBeCreatedWithAnExpectationSet()
    {

        $expectations = new Collection;
        $expectations->push((new Expectation)->method(HttpMethod::GET)->replyWith(new Response));
        $client = MuzzleBuilder::create($expectations)->build();

        $this->assertEquals($expectations, $client->expectations());
        $client->get('/');
    }

    /** @test */
    public function itCanEnqueueAnExpectationSet()
    {

        $expectations = new Collection;
        $expectations->push((new Expectation)->method(HttpMethod::GET)
                                             ->replyWith(new Response));
        $expectations->push((new Expectation)->method(HttpMethod::POST)
                                             ->replyWith(new Exception));
        $client = MuzzleBuilder::create()
                               ->expect((new Expectation)->method(HttpMethod::GET)->replyWith(new Response))
                               ->expect((new Expectation)->method(HttpMethod::POST)->replyWith(new Exception))
                               ->build();

        $this->assertEquals($expectations, $client->expectations());
        $client->get('/');
        $this->expectException(Exception::class);
        $client->post('/');
    }

    /** @test */
    public function itCanUseAChainingMethodToBuildRequests()
    {

        $expectations = new Collection;
        $expectations->push(
            (new Expectation)
                ->method(HttpMethod::GET)
                ->uri('/')
                ->replyWith(new Response(HttpStatus::ACCEPTED))
        );
        $expectations->push(
            (new Expectation)
                ->method(HttpMethod::POST)
                ->uri('/foo')
                ->replyWith(new Exception)
        );

        $client = MuzzleBuilder::create()
                               ->get('/')->replyWith(new Response(HttpStatus::ACCEPTED))
                               ->post('/foo')->replyWith(new Exception)
                               ->build();

        array_map(function (Expectation $expected, Expectation $actual) {

            $this->assertEquals($expected->assertions(), $actual->assertions());
        }, $expectations->toArray(), $client->expectations()->toArray());

        $client->get('/');
        $this->expectException(Exception::class);
        $client->post('/foo');
    }

    /** @test */
    public function itCanBuildARequestExpectation()
    {

        $client = MuzzleBuilder::create()
                               ->get('/')
                               ->queryShouldEqual(['foo' => 'bar'])
                               ->headers(['Content-Type' => 'application/json', 'Accepts'])
                               ->bodyShouldEqual('testing body')
                               ->build();

        $client->get('/', [
            'headers' => ['Content-Type' => 'application/json', 'Accepts' => 'application/json'],
            'body' => 'testing body',
            'query' => ['foo' => 'bar'],
        ]);

        $expectation = $client->expectations()->first();

        foreach ($expectation->assertions() as $assertion) {
            $assertion($client->firstRequest(), $client);
        }
    }

    /** @test */
    public function itCanBuildARequestExpectationWithJson()
    {

        $client = MuzzleBuilder::create()
                               ->method(HttpMethod::GET)
                               ->uri('/')
                               ->json(['json' => 'testing body'])
                               ->build();

        $client->get('/', [
            'json' => ['json' => 'testing body'],
        ]);

        $expectation = $client->expectations()->first();

        foreach ($expectation->assertions() as $assertion) {
            $assertion($client->firstRequest(), $client);
        }
    }

    /** @test */
    public function itUsesHttpMethodsToCreateRequestBuildersForTheGivenMethod()
    {

        $builder = MuzzleBuilder::create();

        foreach (HttpMethod::toArray() as $method) {
            $builder->{$method}();
        }

        $this->expectException(\BadMethodCallException::class);
        $builder->foo();
    }

    /** @test */
    public function itCanReplaceAClientInterfaceInstanceInTheLaravelContainer()
    {

        $muzzle = MuzzleBuilder::create()->replace();

        $this->assertInstanceOf(Muzzle::class, $muzzle);
        $this->assertEquals('https://example.com', $muzzle->getConfig('base_uri'));
    }

    /** @test */
    public function itCanAddMiddlewareToTheMuzzleInstance()
    {

        $middleware = new Decodable;
        $muzzle = MuzzleBuilder::create()
                               ->withMiddleware($middleware)
                               ->build();

        $stack = $muzzle->getConfig('handler');

        $this->assertContains(Decodable::class, (string) $stack);
    }
}
