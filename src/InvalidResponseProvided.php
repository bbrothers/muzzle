<?php

namespace Muzzle;

use InvalidArgumentException;
use function GuzzleHttp\describe_type;

class InvalidResponseProvided extends InvalidArgumentException
{

    public static function fromValue($value) : self
    {

        return new static('Expected a response or exception. Found ' . describe_type($value));
    }
}
