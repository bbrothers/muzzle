<?php

namespace Muzzle;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Muzzle\Assertions\AssertionRules;
use Muzzle\Messages\Transaction;
use Muzzle\Middleware\Assertable;
use Muzzle\Middleware\History;

class Muzzle implements ClientInterface
{

    use WrapsGuzzle;

    /**
     * @var Transactions
     */
    protected $history;
    /**
     * @var ClientInterface
     */
    protected $client;
    /**
     * @var HandlerStack
     */
    protected $stack;
    /**
     * @var MockHandler
     */
    protected $handler;
    /**
     * @var Transactions
     */
    protected $transactions;
    protected $assertionsHaveRun;

    public function __construct(array $options = [])
    {

        $this->transactions = new Transactions;
        $this->handler = new MockHandler;
        $this->stack = HandlerStack::create($this->handler);
        $this->client = new GuzzleClient(array_merge($options, ['handler' => $this->stack]));
        $this->setHistory(new Transactions);
        $this->stack->push(new Assertable, 'assertable');
        $this->stack->push(new History($this->history()), 'history');

        Container::push($this);
    }

    public static function make(array $options = []) : Muzzle
    {

        return new static($options);
    }

    public static function builder() : MuzzleBuilder
    {

        return new MuzzleBuilder;
    }

    public function updateConfig(array $config) : Muzzle
    {

        $this->client = new GuzzleClient(array_merge(
            $this->client->getConfig(),
            array_except($config, ['handler'])
        ));

        return $this;
    }

    public function makeAssertions() : void
    {

        $this->assertionsHaveRun = true;
        AssertionRules::new($this)->runAssertions();
    }

    public function append(Transaction ...$transactions) : Muzzle
    {

        foreach ($transactions as $transaction) {
            $this->transactions->push($transaction);

            $this->handler->append($transaction->response() ?: $transaction->error());
        }

        return $this;
    }

    public function addMiddleware(callable ...$middlewares) : Muzzle
    {

        foreach ($middlewares as $middleware) {
            $this->stack->push($middleware);
        }

        return $this;
    }

    public function removeMiddleware(string $middleware) : Muzzle
    {

        $this->stack->remove($middleware);

        return $this;
    }

    public function setHistory(Transactions $history) : Muzzle
    {

        $this->history = $history;

        return $this;
    }

    public function history() : Transactions
    {

        return $this->history;
    }

    public function expectations() : Transactions
    {

        return $this->transactions;
    }

    public function __destruct()
    {

        if (! $this->assertionsHaveRun) {
            $this->makeAssertions();
        }
    }
}
