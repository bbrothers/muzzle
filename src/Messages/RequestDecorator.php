<?php

namespace Muzzle\Messages;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

trait RequestDecorator
{

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {

        $this->request = $request;
    }

    /**
     * @param RequestInterface $request
     * @return RequestInterface|AssertableRequest
     */
    public static function fromBaseRequest(RequestInterface $request) : RequestInterface
    {

        if ($request instanceof static) {
            return $request;
        }

        return new static($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {

        return $this->request->getProtocolVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {

        return static::fromBaseRequest($this->request->withProtocolVersion($version));
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {

        return $this->request->getHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {

        return $this->request->hasHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {

        return $this->request->getHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {

        return $this->request->getHeaderLine($name);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {

        return static::fromBaseRequest($this->request->withHeader($name, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {

        return static::fromBaseRequest($this->request->withAddedHeader($name, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {

        return static::fromBaseRequest($this->request->withoutHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {

        return $this->request->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {

        return static::fromBaseRequest($this->request->withBody($body));
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget()
    {

        return $this->request->getRequestTarget();
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {

        return static::fromBaseRequest($this->request->withRequestTarget($requestTarget));
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {

        return $this->request->getMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {

        return static::fromBaseRequest($this->request->withMethod($method));
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {

        return $this->request->getUri();
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {

        return static::fromBaseRequest($this->request->withUri($uri, $preserveHost));
    }
}
