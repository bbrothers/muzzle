<?php

namespace Muzzle;

use InvalidArgumentException;
use function GuzzleHttp\describe_type;

class ValueNotCallable extends InvalidArgumentException
{

    public static function onHeaders($value)
    {

        return new static(sprintf(
            'The [on_headers] configuration value must be callable, [%s] given.',
            describe_type($value)
        ));
    }
}
