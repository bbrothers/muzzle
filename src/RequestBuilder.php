<?php

namespace Muzzle;

use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\parse_query;

class RequestBuilder
{

    /**
     * @var HttpMethod
     */
    private $method;
    /**
     * @var Uri
     */
    private $uri;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var string
     */
    private $body;
    /**
     * @var array
     */
    private $query = [];
    /**
     * @var ResponseBuilder|\Psr\Http\Message\ResponseInterface
     */
    private $reply;


    /**
     * @param HttpMethod|null $method                                      HttpStatus method
     * @param string|null $uri                                             URI
     * @param array $headers                                               Request headers
     * @param string|null|resource|\Psr\Http\Message\StreamInterface $body Request body
     */
    public function __construct(
        HttpMethod $method = null,
        ?string $uri = null,
        array $headers = [],
        $body = null
    ) {

        $this->setMethod($method ?: HttpMethod::GET());
        $this->setUri($uri);
        $this->setHeaders($headers);
        $this->setBody($body);
    }

    public function setMethod(HttpMethod $method) : RequestBuilder
    {

        $this->method = $method;

        return $this;
    }

    public function setUri(?string $uri) : RequestBuilder
    {

        $this->uri = new Uri($uri ?: '');

        return $this;
    }

    public function setHeaders(array $headers = []) : RequestBuilder
    {

        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string|null|resource|StreamInterface $body
     * @return RequestBuilder
     */
    public function setBody($body) : RequestBuilder
    {

        $this->body = $body;

        return $this;
    }

    public function setQuery(array $query = []) : RequestBuilder
    {

        $this->query = $query;

        return $this;
    }

    /**
     * @param ResponseBuilder|ResponseInterface|Exception $reply
     * @return RequestBuilder
     */
    public function replyWith($reply = null) : RequestBuilder
    {

        $this->reply = $reply ?: new ResponseBuilder;

        return $this;
    }

    /**
     * @return ResponseInterface|ResponseBuilder
     */
    public function reply()
    {

        return $this->reply ?: $this->replyWith()->reply();
    }

    public function build() : Request
    {

        $uri = $this->uri->withQuery(build_query(array_merge(parse_query($this->uri->getQuery()), $this->query)));

        return new Request($this->method->getValue(), $uri, $this->headers, $this->body);
    }
}
