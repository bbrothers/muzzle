<?php

namespace Muzzle;

use GuzzleHttp\Psr7\Response;
use Muzzle\Messages\DecodableResponse;
use Muzzle\Messages\Fixture;
use Muzzle\Messages\HtmlFixture;
use Muzzle\Messages\JsonFixture;
use Psr\Http\Message\ResponseInterface;

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
     * @var string|null|resource|\Psr\Http\Message\StreamInterface
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

    public static function fromFixture(
        string $fixture,
        int $status = HttpStatus::OK,
        array $headers = []
    ) : ResponseInterface {

        return (new static($status, $headers))
            ->setBodyFromFixture($fixture)
            ->toFixture();
    }

    /**
     * @return Fixture|JsonFixture|HtmlFixture
     */
    public function toFixture() : Fixture
    {

        if (is_json($this->body)) {
            return new JsonFixture($this->status, $this->headers, $this->body);
        }

        return new HtmlFixture($this->status, $this->headers, $this->body);
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

    /**
     * @param mixed $body
     * @return ResponseBuilder
     */
    public function setJson($body) : ResponseBuilder
    {

        $this->body = \GuzzleHttp\json_encode($body);
        return $this;
    }

    public function setBodyFromFixture(string $path) : ResponseBuilder
    {

        $this->body = file_get_contents(static::$fixturePath . ltrim($path, '/'));

        return $this;
    }

    /**
     * @return ResponseInterface|DecodableResponse
     */
    public function build() : ResponseInterface
    {

        $response = new Response($this->status, $this->headers, $this->body);

        return new DecodableResponse($response);
    }
}
