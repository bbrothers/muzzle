<?php

namespace Muzzle;

use Throwable;
use UnexpectedValueException;

class NotATransaction extends UnexpectedValueException
{

    public static function value($value) : Throwable
    {

        return new static(sprintf(
            'Expected an array of transactions but got an array value of type: %s',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}
