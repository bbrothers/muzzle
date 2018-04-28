<?php

namespace Muzzle\Messages;

use ArrayAccess;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Psr7\stream_for;

class JsonFixture implements ResponseInterface, ArrayAccess
{

    use ResponseDecorator {
        __construct as initialize;
    }

    /**
     * @var array
     */
    private $body = [];

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

    public static function fromResponse(ResponseInterface $response) : JsonFixture
    {

        return new static($response->getStatusCode(), $response->getHeaders(), $response->getBody());
    }

    public function getBody()
    {

        return stream_for(json_encode($this->body));
    }

    public function withBody(StreamInterface $body)
    {

        $this->body = json_decode($body, true);
        return $this;
    }

    public function asArray() : array
    {

        return $this->body;
    }

    public function has(string $key) : bool
    {

        return Arr::has($this->body, $key);
    }

    /**
     * @param string $key
     * @param mixed|callable $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {

        return Arr::get($this->body, $key, $default);
    }

    public function set(string $key, $value) : JsonFixture
    {

        Arr::set($this->body, $key, $value);

        return $this;
    }

    public function forget(string $key) : void
    {

        Arr::forget($this->body, $key);
    }

    public function only(array $keys) : array
    {

        return Arr::only($this->body, $keys);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset) : bool
    {

        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {

        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value) : void
    {

        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {

        $this->forget($offset);
    }
}
