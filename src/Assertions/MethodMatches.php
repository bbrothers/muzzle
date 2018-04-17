<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\Transaction;

class MethodMatches implements Assertion
{

    public function assert(Transaction $actual, Transaction $expected) : void
    {

        $actual->request()->assertMethod($expected->request()->getMethod());
    }
}
