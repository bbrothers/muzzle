<?php

namespace Muzzle\Messages;

use ArrayAccess;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Psr7\stream_for;

/**
 * @method static Fixture|JsonFixture fromResponse(ResponseInterface $response)
 * @method static Fixture|JsonFixture fromBaseResponse(ResponseInterface $response)
 */
class JsonFixture extends AbstractFixture implements ArrayAccess
{

    /**
     * @var array
     */
    protected $body = [];

    public function getBody()
    {

        return stream_for(json_encode($this->body));
    }

    public function withBody(StreamInterface $body)
    {

        $this->body = json_decode($body, true);
        $this->saveBody();

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
        $this->saveBody();

        return $this;
    }

    public function forget(string $key) : void
    {

        Arr::forget($this->body, $key);
        $this->saveBody();
    }

    public function only(array $keys) : array
    {

        $withDots = array_combine($keys, array_map(function ($key) {

            return Arr::get($this->body, $key);
        }, $keys));

        $expanded = [];
        foreach ($withDots as $key => $value) {
            Arr::set($expanded, $key, $value);
        }

        return $expanded;
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
