<?php

namespace Muzzle;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Collection;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Middleware\Assertable;
use Muzzle\Middleware\History;

class Muzzle implements ClientInterface
{

    use WrapsGuzzle;

    /**
     * @var Container
     */
    protected static $container;
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
     * @var Collection
     */
    protected $expectations;
    protected $assertionsHaveRun;

    public function __construct(array $options = [])
    {

        $this->expectations = new Collection;
        $this->handler = new MockHandler;
        $this->stack = HandlerStack::create($this->handler);
        $this->client = new GuzzleClient(array_merge($options, ['handler' => $this->stack]));
        $this->setHistory(new Transactions);
        $this->stack->push(new Assertable, 'assertable');
        $this->stack->push(new History($this->history()), 'history');

        static::container()->push($this);
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
        foreach ($this->expectations() as $index => $expectation) {
            foreach ($expectation->assertions() as $assertion) {
                $assertion($this->history()->get($index)->request(), $this);
            }
        }
    }

    public function append(Expectation ...$expectations) : Muzzle
    {

        foreach ($expectations as $expectation) {
            $this->expectations->push($expectation);
            $this->handler->append($expectation->reply());
        }

        return $this;
    }

    public function addMiddleware(callable ...$middlewares) : Muzzle
    {

        foreach ($middlewares as $middleware) {
            $this->stack->before('history', $middleware, is_object($middleware) ? get_class($middleware) : '');
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

    public function lastRequest() : AssertableRequest
    {

        return $this->history->last()->request();
    }

    public function firstRequest() : AssertableRequest
    {

        return $this->history->first()->request();
    }

    /**
     * @return Collection|Expectation[]
     */
    public function expectations() : Collection
    {

        return $this->expectations;
    }

    public static function container() : Container
    {

        if (! static::$container) {
            static::$container = new Container;
        }

        return static::$container;
    }

    public static function close() : void
    {

        if (static::$container === null) {
            return;
        }

        $container = self::$container;
        self::$container = null;
        $container->makeAssertions();
    }

    public static function flush() : void
    {

        if (static::$container !== null) {
            static::$container->flush();
        }

        static::$container = new Container;
    }
}
