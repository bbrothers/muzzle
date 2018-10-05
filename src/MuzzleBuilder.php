<?php

namespace Muzzle;

use BadMethodCallException;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Collection;

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
 * @method MuzzleBuilder should(callable $assertion)
 * @method MuzzleBuilder method(string ...$method)
 * @method MuzzleBuilder uri(string $uri = '/')
 * @method MuzzleBuilder headers(array $headers)
 * @method MuzzleBuilder query(array $expected)
 * @method MuzzleBuilder queryShouldEqual(array $expected)
 * @method MuzzleBuilder body(string $uri = '/')
 * @method MuzzleBuilder bodyShouldEqual($expected)
 * @method MuzzleBuilder json(array $body)
 * @method MuzzleBuilder replyWith($reply = null)
 *
 * @mixin Expectation
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
     * @var Expectation
     */
    private $building;

    public function __construct(Collection $expectations = null)
    {

        $this->expectations = $expectations ?: new Collection;
    }

    public static function create(Collection $transactions = null) : MuzzleBuilder
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

    public function expect(Expectation $expectation) : self
    {

        $this->expectations->push($expectation);

        return $this;
    }

    public function build(array $options = []) : Muzzle
    {

        $this->closeBuilder();
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

    private function builder() : Expectation
    {

        if (! $this->building) {
            $this->building = new Expectation;
        }

        return $this->building;
    }

    private function closeBuilder() : void
    {

        if ($this->building) {
            $this->expect($this->building);
            $this->building = null;
        }
    }

    public function __call($method, $parameters)
    {

        if (HttpMethod::isValid($method)) {
            $this->closeBuilder();
            $this->builder()->method($method)->uri(...$parameters ?: ['/']);
            return $this;
        }

        if (is_callable([$this->builder(), $method])) {
            $this->builder()->{$method}(...$parameters);
            return $this;
        }

        throw new BadMethodCallException(sprintf('The method %s is not defined.', $method));
    }
}
