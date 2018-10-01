<?php

namespace Muzzle;

use Closure;
use Countable;
use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Muzzle\Messages\AssertableRequest;
use OutOfBoundsException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SplQueue;
use function GuzzleHttp\describe_type;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

class MockHandler implements Countable
{

    private $queue;
    /**
     * @var array
     */
    private $expectations;
    /**
     * @var Muzzle
     */
    private $muzzle;

    /**
     * @param ResponseInterface[]|Exception[]|PromiseInterface[]|callable[] $queue
     * @param Collection $expectations
     * @param Muzzle $muzzle
     */
    public function __construct(
        array $queue = [],
        Collection $expectations = null,
        Muzzle $muzzle = null
    ) {

        $this->queue = new SplQueue;
        $this->expectations = $expectations ?: new Collection;
        $this->muzzle = $muzzle;
        $this->append(...$queue);
    }

    public function __invoke(RequestInterface $request, array $options)
    {

        $this->applyDelay($options);

        $expectation = $this->expectations->shift() ?: new Expectation;

        foreach ($expectation->assertions() as $assertion) {
            $assertion(
                AssertableRequest::fromBaseRequest($request),
                $this->muzzle ?: new Muzzle
            );
        }

        if ($this->queue->isEmpty()) {
            throw new OutOfBoundsException('Mock queue is empty');
        }

        return $this->resolveResponse($request, $options)->then(
            $this->onFulfilled($request, $options),
            $this->onRejected($request, $options)
        );
    }

    /**
     * Adds one or more variadic requests, exceptions, callables, or promises
     * to the queue.
     * @param array $responses
     */
    public function append(...$responses) : void
    {

        foreach ($responses as $value) {
            $this->assertQueueableResponse($value);
            $this->queue->enqueue($value);
        }
    }

    public function expect(Expectation ...$expectations) : void
    {

        foreach ($expectations as $expectation) {
            $this->expectations->push($expectation);
        }
    }

    public function count() : int
    {

        return count($this->queue);
    }

    public function setMuzzle(Muzzle $muzzle) : self
    {

        $this->muzzle = $muzzle;

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return Closure
     */
    private function onFulfilled(RequestInterface $request, array $options) : Closure
    {

        return function ($value) use ($request, $options) {

            $this->invokeStats($request, $options, $value);
            $this->sink($value, $options);

            return $value;
        };
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return Closure
     */
    private function onRejected(RequestInterface $request, array $options) : Closure
    {

        return function ($reason) use ($request, $options) {

            $this->invokeStats($request, $options, null, $reason);

            return rejection_for($reason);
        };
    }

    private function invokeStats(
        RequestInterface $request,
        array $options,
        ResponseInterface $response = null,
        $reason = null
    ) : void {

        if (! isset($options['on_stats'])) {
            return;
        }

        $stats = new TransferStats($request, $response, $options['tx_time'] ?? 0, $reason);
        $options['on_stats']($stats);
    }

    /**
     * @param $value
     */
    private function assertQueueableResponse($value) : void
    {

        if (! $this->isQueueableResponse($value)) {
            throw new InvalidArgumentException(
                'Expected a response or exception. Found ' . describe_type($value)
            );
        }
    }

    private function isQueueableResponse($value) : bool
    {

        return $value instanceof ResponseInterface
               || $value instanceof Exception
               || $value instanceof PromiseInterface
               || is_callable($value);
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @param ResponseInterface|Exception|PromiseInterface|callable $response
     * @return ResponseInterface|Exception|PromiseInterface|callable
     */
    private function onHeadersEvent(RequestInterface $request, array $options, $response)
    {

        if (! isset($options['on_headers'])) {
            return $response;
        }

        if (! is_callable($options['on_headers'])) {
            throw new InvalidArgumentException('on_headers must be callable');
        }

        try {
            $options['on_headers']($response);
        } catch (Exception $exception) {
            $message = 'An error was encountered during the on_headers event';
            $response = new RequestException($message, $request, $response, $exception);
        }

        return $response;
    }

    public function sink(ResponseInterface $value, array $options = []) : void
    {

        if (! isset($options['sink'])) {
            return;
        }

        $contents = (string) $value->getBody();
        $sink = $options['sink'];

        if (is_resource($sink)) {
            fwrite($sink, $contents);
        } elseif (is_string($sink)) {
            file_put_contents($sink, $contents);
        } elseif ($sink instanceof StreamInterface) {
            $sink->write($contents);
        }
    }

    private function applyDelay(array $options) : void
    {

        if (isset($options['delay'])) {
            usleep($options['delay'] * 1000);
        }
    }

    private function resolveResponse(RequestInterface $request, array $options) : PromiseInterface
    {

        $response = $this->queue->shift();
        $response = $this->onHeadersEvent($request, $options, $response);

        if (is_callable($response)) {
            $response = $response($request, $options);
        }

        if ($response instanceof Exception) {
            return rejection_for($response);
        }

        return promise_for($response);
    }
}
