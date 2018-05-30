<?php

namespace Muzzle;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Messages\Transaction;
use Muzzle\Middleware\Assertable;
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
    public function itCanBeCreatedWithATransactionSet()
    {

        $transactions = new Transactions;
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::GET, '/'))
                                             ->setResponse(new Response));
        $client = MuzzleBuilder::create($transactions)->build();

        $this->assertEquals($transactions, $client->expectations());
        $client->get('/');
    }

    /** @test */
    public function itCanEnqueueATransactionSet()
    {

        $transactions = new Transactions;
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::GET, '/'))
                                             ->setResponse(new Response));
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::POST, '/'))
                                             ->setError(new Exception));
        $client = MuzzleBuilder::create()
                               ->enqueue(new Request(HttpMethod::GET, '/'), new Response)
                               ->enqueue(new Request(HttpMethod::POST, '/'), new Exception)
                               ->build();

        $this->assertEquals($transactions, $client->expectations());
        $client->get('/');
        $this->expectException(Exception::class);
        $client->post('/');
    }

    /** @test */
    public function itCanUseAChainingMethodToBuildRequests()
    {

        $transactions = new Transactions;
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::GET, '/'))
                                             ->setResponse(new Response(HttpStatus::ACCEPTED)));
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::POST, '/foo'))
                                             ->setError(new Exception));

        $client = MuzzleBuilder::create()
                               ->get('/')->replyWith(new Response(HttpStatus::ACCEPTED))
                               ->post('/foo')->replyWith(new Exception)
                               ->build();

        $this->assertEquals($transactions, $client->expectations());
        $client->get('/');
        $this->expectException(Exception::class);
        $client->post('/foo');
    }

    /** @test */
    public function itCanBuildARequestExpectation()
    {

        $client = MuzzleBuilder::create()
                               ->setMethod(HttpMethod::GET())
                               ->setUri('/')
                               ->setQuery(['foo' => 'bar'])
                               ->setHeaders(['Content-Type' => 'application/json'])
                               ->setBody('testing body')
                               ->withMiddleware(new Assertable)
                               ->build();

        $client->get('/', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => 'testing body',
            'query' => ['foo' => 'bar'],
        ]);

        $request = $client->expectations()->first()->request();


        (new AssertableRequest($request))
            ->assertMethod(HttpMethod::GET)
            ->assertUriPath('/')
            ->assertUriQueryContains(['foo' => 'bar'])
            ->assertHeader('Content-Type', 'application/json')
            ->assertBodyEquals('testing body');
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
}
