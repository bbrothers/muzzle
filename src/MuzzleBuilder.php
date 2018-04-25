<?php

namespace Muzzle;

use BadMethodCallException;
use Exception;
use GuzzleHttp\ClientInterface;
use Muzzle\Messages\Transaction;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @method MuzzleBuilder connect(string $uri = '/')
 * @method MuzzleBuilder delete(string $uri = '/')
 * @method MuzzleBuilder get(string $uri = '/')
 * @method MuzzleBuilder head(string $uri = '/')
 * @method MuzzleBuilder options(string $uri = '/')
 * @method MuzzleBuilder patch(string $uri = '/')
 * @method MuzzleBuilder post(string $uri = '/')
 * @method MuzzleBuilder put(string $uri = '/')
 * @method MuzzleBuilder trace(string $uri = '/')
 */
class MuzzleBuilder
{

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var Transactions
     */
    private $expectations;

    /**
     * @var callable[]
     */
    private $middleware = [];

    /**
     * @var RequestBuilder
     */
    private $building;

    public function __construct(Transactions $expectations = null)
    {

        $this->expectations = $expectations ?: new Transactions;
    }

    public static function create(Transactions $transactions = null) : MuzzleBuilder
    {

        return new static($transactions);
    }

    public function withOptions(array $options) : MuzzleBuilder
    {

        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function withMiddleware(callable ...$middleware) : MuzzleBuilder
    {

        $this->middleware = array_merge($this->middleware, $middleware);

        return $this;
    }

    /**
     * @param RequestInterface|RequestBuilder $request
     * @param ResponseInterface|ResponseBuilder|Exception $response
     * @return MuzzleBuilder
     */
    public function enqueue($request, $response) : self
    {

        $transaction = new Transaction;
        $transaction->setRequest($request instanceof RequestBuilder ? $request->build() : $request);
        $transaction->setResponseOrError($response instanceof ResponseBuilder ? $response->build() : $response);

        $this->expectations->push($transaction);

        return $this;
    }

    public function buildRequest(HttpMethod $method, string $uri = '/') : MuzzleBuilder
    {

        $builder = new RequestBuilder($method, $uri);
        if ($this->building) {
            $this->enqueue($this->building, $this->building->reply());
        }
        $this->building = $builder;

        return $this;
    }

    public function build(array $options = []) : Muzzle
    {

        if ($this->building) {
            $this->enqueue($this->building, $this->building->reply());
            $this->building = null;
        }
        $this->withOptions($options);


        return Muzzle::make($options)
                     ->append(...$this->expectations)
                     ->addMiddleware(...$this->middleware);
    }

    public function replace(array $options = []) : Muzzle
    {

        $muzzle = $this->build($options);

        if (function_exists('app') or function_exists(__NAMESPACE__ . '\\app')) {
            app()->extend(ClientInterface::class, function ($guzzle) use ($muzzle) {

                return $muzzle->updateConfig($guzzle->getConfig());
            });
        }

        return $muzzle;
    }

    private function builder() : RequestBuilder
    {

        if (! $this->building) {
            $this->building = new RequestBuilder;
        }

        return $this->building;
    }

    public function setMethod(HttpMethod $method) : MuzzleBuilder
    {

        $this->builder()->setMethod($method);

        return $this;
    }

    public function setUri(?string $uri) : MuzzleBuilder
    {

        $this->builder()->setUri($uri);

        return $this;
    }

    public function setHeaders(array $headers = []) : MuzzleBuilder
    {

        $this->builder()->setHeaders($headers);

        return $this;
    }

    public function setBody($body) : MuzzleBuilder
    {

        $this->builder()->setBody($body);

        return $this;
    }

    public function setQuery(array $query = []) : MuzzleBuilder
    {

        $this->builder()->setQuery($query);

        return $this;
    }

    public function replyWith($reply = null) : MuzzleBuilder
    {

        $this->builder()->replyWith($reply);

        return $this;
    }

    public function __call($method, $parameters)
    {

        if (HttpMethod::isValid($method)) {
            return $this->buildRequest(new HttpMethod($method), ...$parameters);
        }

        throw new BadMethodCallException(sprintf('The method %s is not defined.', $method));
    }
}
