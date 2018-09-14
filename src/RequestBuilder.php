<?php

namespace Muzzle;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\StreamInterface;
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
     * @var string|null|resource|\Psr\Http\Message\StreamInterface
     */
    private $body;
    /**
     * @var array
     */
    private $query = [];


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

    public function setJson(array $body) : RequestBuilder
    {

        $this->body = json_encode($body);

        return $this;
    }

    public function setQuery(array $query = []) : RequestBuilder
    {

        $this->query = $query;

        return $this;
    }

    public function build() : Request
    {

        $uri = $this->uri->withQuery(http_build_query(array_merge(
            parse_query($this->uri->getQuery()),
            $this->query
        )));

        return new Request($this->method->getValue(), $uri, $this->headers, $this->body);
    }
}
