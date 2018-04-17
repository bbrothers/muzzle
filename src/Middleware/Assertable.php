<?php

namespace Muzzle\Middleware;

use Muzzle\Messages\AssertableRequest;
use Muzzle\Messages\AssertableResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Assertable
{

    public function __invoke(callable $handler)
    {

        return function (RequestInterface $request, array $options = []) use ($handler) {

            return $handler(AssertableRequest::fromBaseRequest($request), $options)->then(
                function (ResponseInterface $response) {

                    return AssertableResponse::fromBaseResponse($response);
                }
            );
        };
    }
}
