<?php

namespace Muzzle\Messages;

use ArrayAccess;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Prophecy\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Transaction implements ArrayAccess
{

    protected $request;
    protected $response;
    protected $error;
    protected $options;

    public static function new()
    {

        return new static;
    }

    /**
     * @param RequestInterface $request
     * @return Transaction
     */
    public function setRequest(RequestInterface $request) : Transaction
    {

        $this->request = $request;

        return $this;
    }

    /**
     * @return RequestInterface|AssertableRequest
     */
    public function request() : RequestInterface
    {

        return $this->request;
    }

    /**
     * @param ResponseInterface|PromiseInterface $response
     * @return Transaction
     */
    public function setResponse($response) : Transaction
    {

        $this->response = $response;

        return $this;
    }

    public function setResponseOrError($response) : Transaction
    {

        if ($response instanceof Exception) {
            return $this->setError($response);
        }

        return $this->setResponse($response);
    }

    /**
     * @return ResponseInterface|AssertableResponse|PromiseInterface
     */
    public function response()
    {

        return $this->response;
    }

    /**
     * @param Throwable $error
     * @return Transaction
     */
    public function setError(Throwable $error) : Transaction
    {

        $this->error = $error;
        return $this;
    }

    /**
     * @return GuzzleException
     */
    public function error()
    {

        return $this->error;
    }

    /**
     * @param array $options
     * @return Transaction
     */
    public function setOptions(array $options) : Transaction
    {

        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function options() : array
    {

        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->{$offset});
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (! $this->offsetExists($offset)) {
            return null;
        }

        return $this->{$offset};
    }

    /**
     * {@inheritdoc}
     * @return self
     */
    public function offsetSet($offset, $value) : Transaction
    {
        $this->{$offset} = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset) : void
    {
        unset($this->{$offset});
    }
}
