<?php

namespace Muzzle;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Muzzle\Assertions\Assertion;
use Muzzle\Assertions\BodyMatches;
use Muzzle\Assertions\CallbackAssertion;
use Muzzle\Assertions\HeadersMatch;
use Muzzle\Assertions\MethodMatches;
use Muzzle\Assertions\QueryContains;
use Muzzle\Assertions\QueryEquals;
use Muzzle\Assertions\UriPathMatches;
use Muzzle\Messages\AssertableRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Throwable;

class Expectation
{

    /**
     * @var Assertion[]
     */
    protected $assertions = [];

    /**
     * @var ResponseInterface|ResponseBuilder|Throwable
     */
    protected $reply;

    /**
     * @param string|null $method                        HttpStatus method
     * @param string|null $uri                           URI
     * @param array $headers                             Request headers
     * @param string|null|resource|StreamInterface $body Request body
     */
    public function __construct(
        string $method = null,
        ?string $uri = null,
        array $headers = [],
        $body = null
    ) {

        $this->method($method);
        $this->uri($uri);
        $this->headers($headers);
        $this->body($body);
    }

    /**
     * @return Assertion[]
     */
    public function assertions() : array
    {

        return array_values($this->assertions);
    }

    public function assertion(string $key) : ?Assertion
    {

        return $this->assertions[$key] ?? null;
    }

    /**
     * @return ResponseInterface|Throwable
     */
    public function reply()
    {

        if ($this->reply instanceof ResponseBuilder) {
            return $this->reply->build();
        }

        return $this->reply ?: $this->replyWith()->reply();
    }

    public function should(callable $assertion) : self
    {

        if (! $assertion instanceof Assertion) {
            $assertion = new CallbackAssertion($assertion);
        }

        $this->assertions[spl_object_hash($assertion)] = $assertion;

        return $this;
    }

    public function method(?string ...$methods) : self
    {

        if (! empty(array_filter($methods))) {
            $this->assertions['method'] = new MethodMatches(...$methods);
        }

        return $this;
    }

    public function uri(?string $uri) : self
    {

        if ($uri) {
            $this->assertions['uri'] = new UriPathMatches($uri);
        }

        return $this;
    }

    public function headers(?array $headers) : self
    {

        if (! empty($headers)) {
            $this->assertions['headers'] = new HeadersMatch($headers);
        }

        return $this;
    }

    public function query(?array $query) : self
    {

        if (! empty($query)) {
            $this->assertions['query'] = new QueryContains($query);
        }

        return $this;
    }

    public function queryShouldEqual(?array $query) : self
    {

        if (! empty($query)) {
            $this->assertions['query'] = new QueryEquals($query);
        }

        return $this;
    }

    /**
     * @param StreamInterface|array|string $body
     * @return Expectation
     */
    public function body($body) : self
    {

        if ($body) {
            $this->assertions['body'] = new BodyMatches($body);
        }

        return $this;
    }

    public function bodyShouldEqual($body) : self
    {

        if (! $body) {
            return $this;
        }

        return $this->should(function (AssertableRequest $request) use ($body) {

            $request->assertBodyEquals($body);
        });
    }

    public function json(?array $body) : self
    {

        return $this->body($body);
    }

    /**
     * @param ResponseBuilder|ResponseInterface|Throwable $reply
     * @return Expectation
     */
    public function replyWith($reply = null) : self
    {

        if ($reply and ! $this->isQueueableResponse($reply)) {
            throw InvalidResponseProvided::fromValue($reply);
        }

        $this->reply = $reply ?: new ResponseBuilder;

        return $this;
    }

    private function isQueueableResponse($value) : bool
    {

        return $value instanceof ResponseInterface
               || $value instanceof ResponseBuilder
               || $value instanceof Exception
               || $value instanceof PromiseInterface
               || is_callable($value);
    }
}
