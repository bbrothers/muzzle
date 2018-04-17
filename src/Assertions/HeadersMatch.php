<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\Transaction;

class HeadersMatch implements Assertion
{

    public function assert(Transaction $actual, Transaction $expected) : void
    {

        foreach ($expected->request()->getHeaders() as $header => $value) {
            $actual->request()->assertHeader($header, $value);
        }
    }
}
