<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\Transaction;

class UriPathMatches implements Assertion
{

    public function assert(Transaction $actual, Transaction $expected) : void
    {

        $actual->request()->assertUriPath($expected->request()->getUri()->getPath());
    }
}
