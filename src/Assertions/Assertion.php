<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\Transaction;

interface Assertion
{

    public function assert(Transaction $actual, Transaction $expected) : void;
}
