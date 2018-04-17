<?php

namespace Muzzle\Middleware;

use Muzzle\Messages\AssertableRequest;
use Muzzle\Messages\AssertableResponse;
use Muzzle\Messages\Transaction;
use Muzzle\Transactions;

class History
{

    private $container;

    public function __construct(Transactions $container)
    {

        $this->container = $container;
    }

    public function __invoke(callable $handler)
    {

        return function ($request, array $options = []) use ($handler) {

            return $handler($request, $options)->then(
                function ($value) use ($request, $options) {

                    $transaction = (new Transaction)
                        ->setRequest(AssertableRequest::fromBaseRequest($request))
                        ->setResponse(AssertableResponse::fromBaseResponse($value))
                        ->setOptions($options);
                    $this->container->push($transaction);

                    return $value;
                },
                function ($reason) use ($request, $options) {

                    $transaction = (new Transaction)
                        ->setRequest(AssertableRequest::fromBaseRequest($request))
                        ->setError($reason)
                        ->setOptions($options);
                    $this->container->push($transaction);

                    return \GuzzleHttp\Promise\rejection_for($reason);
                }
            );
        };
    }
}
