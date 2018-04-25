<?php

namespace Muzzle;

use GuzzleHttp\Psr7\Response;
use Muzzle\Messages\JsonFixture;
use Psr\Http\Message\ResponseInterface;

/**
 * @param string $fixture
 * @param int $status
 * @param array $headers
 * @return Response|JsonFixture
 */
function fixture(string $fixture, int $status = HttpStatus::OK, array $headers = []) : ResponseInterface
{

    return ResponseBuilder::fromFixture($fixture, $status, $headers);
}
