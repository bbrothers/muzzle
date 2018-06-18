<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\Transaction;

class RequestContainsJson implements Assertion
{

    public function assert(Transaction $actual, Transaction $expected) : void
    {

        $actual->request()->assertJson(
            (array) json_decode($expected->request()->getBody(), true)
        );
    }
}
