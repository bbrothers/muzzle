<?php

namespace Muzzle\Messages;

use Psr\Http\Message\ResponseInterface;

interface Fixture extends ResponseInterface
{

    public static function fromResponse(ResponseInterface $response) : Fixture;
}
