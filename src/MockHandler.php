<?php

namespace Muzzle;

use Closure;
use Countable;
use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Collection;
use Muzzle\Messages\AssertableRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

class MockHandler implements Countable
{

    /**
     * @var ResponseQueue
     */
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
    public function __construct(array $queue = [], Collection $expectations = null, Muzzle $muzzle = null)
    {

        $this->queue = new ResponseQueue;
        $this->expectations = $expectations ?: new Collection;
        $this->muzzle = $muzzle;
        $this->append(...$queue);
    }

    public function __invoke(RequestInterface $request, array $options) : PromiseInterface
    {

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

        return $this->queue->count();
    }

    public function setMuzzle(Muzzle $muzzle) : self
    {

        $this->muzzle = $muzzle;

        return $this;
    }

    private function compareAgainstExpectations(RequestInterface $request) : void
    {

        $expectation = $this->expectations->shift() ?: new Expectation;

        foreach ($expectation->assertions() as $assertion) {
            $assertion(
                AssertableRequest::fromBaseRequest($request),
                $this->muzzle ?: new Muzzle
            );
        }
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

        if (! isset($options[RequestOptions::ON_STATS])) {
            return;
        }

        $stats = new TransferStats($request, $response, $options['tx_time'] ?? 0, $reason);
        $options[RequestOptions::ON_STATS]($stats);
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @param ResponseInterface|Exception|PromiseInterface|callable $response
     * @return ResponseInterface|Exception|PromiseInterface|callable
     */
    private function onHeadersEvent(RequestInterface $request, array $options, $response)
    {

        if (! isset($options[RequestOptions::ON_HEADERS])) {
            return $response;
        }

        if (! is_callable($options[RequestOptions::ON_HEADERS])) {
            throw ValueNotCallable::onHeaders($options[RequestOptions::ON_HEADERS]);
        }

        try {
            $options[RequestOptions::ON_HEADERS]($response);
        } catch (Exception $exception) {
            $message = 'An error was encountered during the on_headers event';
            $response = new RequestException($message, $request, $response, $exception);
        }

        return $response;
    }

    public function sink(ResponseInterface $value, array $options = []) : void
    {

        if (! isset($options[RequestOptions::SINK])) {
            return;
        }

        $contents = (string) $value->getBody();
        $sink = $options[RequestOptions::SINK];

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

        if (isset($options[RequestOptions::DELAY])) {
            usleep($options[RequestOptions::DELAY] * 1000);
        }
    }

    private function resolveResponse(RequestInterface $request, array $options) : PromiseInterface
    {

        $this->applyDelay($options);

        $this->compareAgainstExpectations($request);

        if ($this->queue->isEmpty()) {
            throw UnexpectedRequestWasMade::fromRequest($request);
        }

        $response = $this->queue->dequeue();
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
