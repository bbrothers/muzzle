<?php

namespace Muzzle\Messages;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class AbstractFixture implements Fixture
{

    use ResponseDecorator {
        __construct as initialize;
    }

    protected $body;

    /**
     * @param int $status                                Status code
     * @param array $headers                             Response headers
     * @param string|null|resource|StreamInterface $body Response body
     * @param string $version                            Protocol version
     * @param string|null $reason                        Reason phrase
     */
    public function __construct(
        $status = 200,
        array $headers = [],
        $body = null,
        $version = '1.1',
        $reason = null
    ) {

        $this->initialize(new Response($status, $headers, $body, $version, $reason));
        $this->withBody($this->response->getBody());
    }

    public static function fromResponse(ResponseInterface $response) : Fixture
    {

        return new static($response->getStatusCode(), $response->getHeaders(), $response->getBody());
    }

    public static function fromBaseResponse(ResponseInterface $response) : Fixture
    {

        return static::fromResponse($response);
    }

    public function __toString() : string
    {

        return (string) $this->getBody();
    }

    protected function saveBody() : void
    {

        $this->initialize($this->response->withBody($this->getBody()));
    }
}
