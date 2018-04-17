<?php

namespace Muzzle\Middleware;

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
}
