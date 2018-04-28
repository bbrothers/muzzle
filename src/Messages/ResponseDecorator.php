<?php

namespace Muzzle\Messages;

use BadMethodCallException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait ResponseDecorator
{

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(ResponseInterface $response)
    {

        $this->response = $response;
    }

    public static function fromBaseResponse(ResponseInterface $response) : ResponseInterface
    {

        if ($response instanceof static) {
            return $response;
        }

        return new static($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {

        return $this->response->getProtocolVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {

        return static::fromBaseResponse($this->response->withProtocolVersion($version));
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {

        return $this->response->getHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {

        return $this->response->hasHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {

        return $this->response->getHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {

        return $this->response->getHeaderLine($name);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {

        return static::fromBaseResponse($this->response->withHeader($name, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {

        return static::fromBaseResponse($this->response->withAddedHeader($name, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {

        return static::fromBaseResponse($this->response->withoutHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {

        return $this->response->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {

        return static::fromBaseResponse($this->response->withBody($body));
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {

        return $this->response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {

        return static::fromBaseResponse($this->response->withStatus($code, $reasonPhrase));
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {

        return $this->response->getReasonPhrase();
    }

    public function __get($property)
    {

        if (property_exists($this->response, $property)) {
            return $this->response->{$property};
        }

        return null;
    }

    public function __call($method, $arguments)
    {

        if (method_exists($this->response, $method)) {
            $response = $this->response->{$method}(...$arguments);
            if (starts_with($method, 'with')) {
                return static::fromBaseResponse($response);
            }
            return $response;
        }

        throw new BadMethodCallException("The method [{$method}] is not defined.");
    }

    /**
     * Proxy isset() checks to the underlying base response.
     *
     * @param  string $key
     * @return mixed
     */
    public function __isset($key)
    {

        return isset($this->response->{$key});
    }
}
