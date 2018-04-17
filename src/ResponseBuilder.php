<?php

namespace Muzzle;

use GuzzleHttp\Psr7\Response;

class ResponseBuilder
{

    /**
     * @var string
     */
    protected static $fixturePath;

    /**
     * @var int|null
     */
    private $status;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var null
     */
    private $body;

    /**
     * @param int|null $status
     * @param array $headers
     * @param string|null|resource|\Psr\Http\Message\StreamInterface $body
     */
    public function __construct(int $status = HttpStatus::OK, array $headers = [], $body = null)
    {

        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    public static function fromFixture(string $fixture, int $status = HttpStatus::OK, array $headers = []) : Response
    {

        return (new static($status, $headers))->setBodyFromFixture($fixture)->build();
    }


    public static function setFixtureDirectory(string $path) : void
    {

        static::$fixturePath = rtrim($path, '/') . '/';
    }

    public function setStatus(?int $status) : ResponseBuilder
    {

        $this->status = $status;
        return $this;
    }

    public function setHeaders(array $headers) : ResponseBuilder
    {

        $this->headers = $headers;
        return $this;
    }

    /**
     * @param string|null|resource|\Psr\Http\Message\StreamInterface $body
     * @return ResponseBuilder
     */
    public function setBody($body) : ResponseBuilder
    {

        $this->body = $body;
        return $this;
    }

    public function setBodyFromFixture(string $path) : ResponseBuilder
    {

        $this->body = fopen(static::$fixturePath . ltrim($path, '/'), 'r');

        return $this;
    }

    public function build() : Response
    {

        return new Response($this->status, $this->headers, $this->body);
    }
}
