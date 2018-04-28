<?php

namespace Muzzle;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Muzzle\Messages\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionsTest extends TestCase
{

    /** @test */
    public function itCanPushAnItemOntoTheEndOfTheTransactionStack()
    {

        $transactions = new Transactions([new Transaction, new Transaction]);

        $last = new Transaction;
        $transactions->push($last);

        $this->assertSame($last, last($transactions->transactions()));
    }

    /** @test */
    public function itCanGetTheLastTransactionOffTheStack()
    {

        $last = new Transaction;
        $transactions = new Transactions([new Transaction, $last]);

        $this->assertSame($last, $transactions->last());
    }

    /** @test */
    public function itCanGetTheFirstTransactionOffTheStack()
    {

        $first = new Transaction;
        $transactions = new Transactions([$first, new Transaction]);

        $this->assertSame($first, $transactions->first());
    }

    /** @test */
    public function itImplementsIteratorAggregate()
    {

        $transactions = new Transactions([new Transaction, new Transaction]);

        $this->assertInstanceOf(IteratorAggregate::class, $transactions);
        $this->assertInstanceOf(ArrayIterator::class, $transactions->getIterator());
    }

    /** @test */
    public function itIsCountable()
    {

        $transactions = new Transactions([new Transaction, new Transaction]);

        $this->assertInstanceOf(Countable::class, $transactions);
        $this->assertCount(2, $transactions);
    }

    /** @test */
    public function itIsArrayAccessible()
    {

        $first = new Transaction;
        $transactions = new Transactions([$first, new Transaction]);
        $transactions[] = new Transaction;
        $transactions['foo'] = new Transaction;

        $this->assertInstanceOf(ArrayAccess::class, $transactions);
        $this->assertCount(4, $transactions);
        $this->assertTrue(isset($transactions['foo']));
        unset($transactions['foo']);
        $this->assertFalse(isset($transactions['foo']));
        $this->assertSame($first, $transactions[0]);
    }
}
