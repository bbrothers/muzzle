<?php

namespace Muzzle\Middleware;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use Muzzle\Messages\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{

    /** @test */
    public function itIsArrayAccessibleToAllowLegacyCallsToContinueToWork()
    {

        $transaction = new Transaction;
        $request = 'request-stub';
        $transaction['request'] = $request;
        $transaction['response'] = 'response-stub';
        unset($transaction['response']);

        $this->assertSame($request, $transaction['request']);
        $this->assertFalse(isset($transaction['response']));
    }

    /** @test */
    public function itReturnsNullForAttributesThatAreNotSet()
    {

        $transaction = new Transaction;
        $this->assertNull($transaction['foo']);
    }

    /** @test */
    public function itCanAcceptAThrowableAResponseInterfaceOrAPromiseInterfaceAsTheResponse()
    {

        $transaction = Transaction::new();
        $transaction->setResponseOrError(new \Exception);
        $this->assertInstanceOf(\Exception::class, $transaction->error());

        $transaction->setResponseOrError(new Promise);
        $this->assertInstanceOf(Promise::class, $transaction->response());

        $transaction->setResponseOrError(new Response);
        $this->assertInstanceOf(Response::class, $transaction->response());
    }
}
