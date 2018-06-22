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

        $this->assertSame($last, $transactions->last());
    }

    /** @test */
    public function itCanPrependAnItemOntoTheTopOfTheTransactionStack()
    {

        $transactions = new Transactions([new Transaction, new Transaction]);

        $first = new Transaction;
        $transactions->prepend($first);

        $this->assertSame($first, $transactions->first());
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

    /** @test */
    public function itCanRetrieveATransactionByKey()
    {

        $first = (new Transaction)->setOptions(['first']);
        $last = (new Transaction)->setOptions(['last']);
        $transactions = new Transactions([$first, new Transaction, $last]);


        $this->assertSame($first, $transactions->get(0));
        $this->assertSame($last, $transactions->get($transactions->count() - 1));
    }

    /** @test */
    public function itCanMapOverTheTransactions()
    {

        $transactions = new Transactions([new Transaction, new Transaction]);
        $transactions = $transactions->map(function (Transaction $transaction) {

            return $transaction->setOptions(['mapped']);
        });

        foreach ($transactions as $transaction) {
            $this->assertEquals(['mapped'], $transaction->options());
        }
    }

    /** @test */
    public function itCanFilterOutTransactions()
    {

        $exclude = (new Transaction)->setOptions(['exclude']);
        $transactions = new Transactions([new Transaction, $exclude, new Transaction]);

        $filtered = $transactions->filter(function (Transaction $transaction) {
            return array_search('exclude', $transaction->options()) === false;
        });

        foreach ($filtered as $transaction) {
            $this->assertNotSame($exclude, $transaction);
        }
    }

    /** @test */
    public function itCanGetATransactionByKeyOrReturnNullIfItsNotFound()
    {

        $second = new Transaction;
        $transactions = new Transactions([new Transaction, $second, new Transaction]);

        $this->assertSame($second, $transactions->get(1));
        $this->assertNull($transactions->get(999));
    }

    /** @test */
    public function itCanCheckIfTheTransactionsCollectionContainsAListOfTransactionsByKeys()
    {

        $transactions = new Transactions([new Transaction, new Transaction, new Transaction]);

        $this->assertTrue($transactions->has(0, 1));
        $this->assertFalse($transactions->has(999));
    }

    /** @test */
    public function itCanReportIfTheTransactionsCollectionIsEmptyOrNot()
    {

        $transactions = new Transactions;
        $this->assertTrue($transactions->isEmpty());
        $this->assertFalse($transactions->isNotEmpty());

        $transactions->push(new Transaction);
        $this->assertFalse($transactions->isEmpty());
        $this->assertTrue($transactions->isNotEmpty());
    }

    /** @test */
    public function itCanReturnAnArrayOfTheContainedTransactions()
    {

        $transactions = [new Transaction, new Transaction, new Transaction];
        $instance = new Transactions($transactions);

        $this->assertSame($transactions, $instance->transactions());
    }
}
