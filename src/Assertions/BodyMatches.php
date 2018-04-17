<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\Transaction;

class BodyMatches implements Assertion
{

    public function assert(Transaction $actual, Transaction $expected) : void
    {

        $actual->request()->assertBodyEquals($expected->request()->getBody());
    }
}
