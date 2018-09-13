<?php

namespace Muzzle;

use Muzzle\Assertions\Assertion;
use Muzzle\Assertions\BodyMatches;
use Muzzle\Assertions\CallbackAssertion;
use Muzzle\Assertions\ExpectedRequestWasMade;
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
     * @param string|null $method                                          HttpStatus method
     * @param string|null $uri                                             URI
     * @param array $headers                                               Request headers
     * @param string|null|resource|\Psr\Http\Message\StreamInterface $body Request body
     */
    public function __construct(
        string $method = null,
        ?string $uri = null,
        array $headers = [],
        $body = null
    ) {

        $this->assertions['happened'] = new ExpectedRequestWasMade($this);
        $this->method($method ?: HttpMethod::GET);
        $this->uri($uri ?: '/');
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

    public function method(string ...$methods) : self
    {

        $this->assertions['method'] = new MethodMatches(...$methods);

        return $this;
    }

    public function uri($uri) : self
    {

        $this->assertions['uri'] = new UriPathMatches($uri);

        return $this;
    }

    public function headers(array $headers) : self
    {

        $this->assertions['headers'] = new HeadersMatch($headers);

        return $this;
    }

    public function query(array $expected) : self
    {

        $this->assertions['query'] = new QueryContains($expected);

        return $this;
    }

    public function queryShouldEqual(array $expected) : self
    {

        $this->assertions['query'] = new QueryEquals($expected);

        return $this;
    }

    /**
     * @param StreamInterface|array|string $expected
     * @return Expectation
     */
    public function body($expected) : self
    {

        $this->assertions['body'] = new BodyMatches($expected);

        return $this;
    }

    public function bodyShouldEqual($expected) : self
    {

        return $this->should(function (AssertableRequest $request) use ($expected) {

            $request->assertBodyEquals($expected);
        });
    }

    public function json(array $body) : self
    {

        return $this->body($body);
    }

    /**
     * @param ResponseBuilder|ResponseInterface|Throwable $reply
     * @return Expectation
     */
    public function replyWith($reply = null) : self
    {

        $this->reply = $reply ?: new ResponseBuilder;

        return $this;
    }

    public function __toString() : string
    {

        $methods = array_reduce($this->assertions(), function ($methods, $assertion) {

            if ($assertion instanceof MethodMatches) {
                $methods = array_merge($methods, $assertion->methods());
            }

            return $methods;
        }, []);

        $uri = array_reduce($this->assertions(), function ($uri, $assertion) {

            if ($assertion instanceof UriPathMatches) {
                return $assertion->uri(new Muzzle);
            }

            return $uri;
        });

        return sprintf('[%s]%s', implode(', ', $methods), $uri);
    }
}
