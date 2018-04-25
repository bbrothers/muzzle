<?php

namespace Muzzle;

use GuzzleHttp\Promise\PromiseInterface;
use Muzzle\Messages\AssertableResponse;
use OutOfBoundsException;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * @method AssertableResponse get(string | UriInterface $uri, array $options = [])
 * @method AssertableResponse head(string | UriInterface $uri, array $options = [])
 * @method AssertableResponse put(string | UriInterface $uri, array $options = [])
 * @method AssertableResponse post(string | UriInterface $uri, array $options = [])
 * @method AssertableResponse patch(string | UriInterface $uri, array $options = [])
 * @method AssertableResponse delete(string | UriInterface $uri, array $options = [])
 * @method PromiseInterface getAsync(string | UriInterface $uri, array $options = [])
 * @method PromiseInterface headAsync(string | UriInterface $uri, array $options = [])
 * @method PromiseInterface putAsync(string | UriInterface $uri, array $options = [])
 * @method PromiseInterface postAsync(string | UriInterface $uri, array $options = [])
 * @method PromiseInterface patchAsync(string | UriInterface $uri, array $options = [])
 * @method PromiseInterface deleteAsync(string | UriInterface $uri, array $options = [])
 */
trait WrapsGuzzle
{

    /**
     * @inheritdoc
     */
    public function send(RequestInterface $request, array $options = []) : ResponseInterface
    {

        return $this->delegate('send', $request, $options);
    }

    /**
     * @inheritdoc
     */
    public function sendAsync(RequestInterface $request, array $options = []) : PromiseInterface
    {

        return $this->delegate('sendAsync', $request, $options);
    }

    /**
     * @inheritdoc
     */
    public function request($method, $uri, array $options = []) : ResponseInterface
    {

        return $this->delegate('request', $method, $uri, $options);
    }

    /**
     * @inheritdoc
     */
    public function requestAsync($method, $uri, array $options = []) : PromiseInterface
    {

        return $this->delegate('requestAsync', $method, $uri, $options);
    }

    /**
     * @inheritdoc
     */
    public function getConfig($option = null)
    {

        return $this->delegate('getConfig', $option);
    }

    public function __call($method, $arguments)
    {

        return $this->delegate($method, ...$arguments);
    }

    private function delegate(string $method, ...$arguments)
    {

        try {
            return $this->client->{$method}(...$arguments);
        } catch (OutOfBoundsException $exception) {
            $dumper = new CliDumper(null, null, AbstractDumper::DUMP_LIGHT_ARRAY);
            PHPUnit::fail(sprintf(
                'Mock queue was empty when calling [%s] with the arguments: %s',
                $method,
                $dumper->dump((new VarCloner)->cloneVar($arguments), true)
            ));
        }
    }
}
