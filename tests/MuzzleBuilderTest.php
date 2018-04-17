<?php

namespace Muzzle;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Muzzle\Messages\Transaction;
use PHPUnit\Framework\TestCase;

class MuzzleBuilderTest extends TestCase
{

    /** @test */
    public function itCanBuildANewClientInstance()
    {

        $this->assertInstanceOf(Muzzle::class, MuzzleBuilder::instance()->build());
    }

    /** @test */
    public function itCanBeCreatedWithATransactionSet()
    {

        $transactions = new Transactions;
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::GET, '/'))
                                             ->setResponse(new Response));
        $client = MuzzleBuilder::instance($transactions)->build();

        $this->assertEquals($transactions, (\Closure::bind(function () {

            return $this->transactions;
        }, $client, $client))(), true);
    }

    /** @test */
    public function itCanEnqueueATransactionSet()
    {

        $transactions = new Transactions;
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::GET, '/'))
                                             ->setResponse(new Response));
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::POST, '/'))
                                             ->setError(new \Exception));
        $client = MuzzleBuilder::instance()
                               ->enqueue(new Request(HttpMethod::GET, '/'), new Response)
                               ->enqueue(new Request(HttpMethod::POST, '/'), new \Exception)
                               ->build();

        $this->assertEquals($transactions, (\Closure::bind(function () {

            return $this->transactions;
        }, $client, $client))(), false);
    }

    /** @test */
    public function itCanUseAChainingMethodToBuildRequests()
    {

        $transactions = new Transactions;
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::GET, '/'))
                                             ->setResponse(new Response(HttpStatus::ACCEPTED)));
        $transactions->push((new Transaction)->setRequest(new Request(HttpMethod::POST, '/foo'))
                                             ->setError(new \Exception));

        $client = MuzzleBuilder::instance()
                               ->get('/')->replyWith(new Response(HttpStatus::ACCEPTED))
                               ->post('/foo')->replyWith(new \Exception)
                               ->build();

        $this->assertEquals($transactions, (\Closure::bind(function () {

            return $this->transactions;
        }, $client, $client))(), false);
    }
}
