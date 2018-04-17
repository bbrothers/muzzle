<?php

namespace Muzzle\Middleware;

use Muzzle\Messages\DecodableResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Decodable
{

    public function __invoke(callable $handler)
    {

        return function (RequestInterface $request, array $options = []) use ($handler) {

            return $handler($request, $options)->then(
                function (ResponseInterface $response) {

                    return new DecodableResponse($response);
                }
            );
        };
    }
}
