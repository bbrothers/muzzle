<?php

namespace Muzzle;

use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

class UnexpectedRequestWasMade extends UnexpectedValueException
{

    public static function fromRequest(RequestInterface $request) : self
    {

        return new static(sprintf(
            'An unexpected request to [%s]%s was made.',
            $request->getMethod(),
            (string) $request->getUri()
        ));
    }
}
