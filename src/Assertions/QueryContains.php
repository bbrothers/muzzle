<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\Transaction;
use function GuzzleHttp\Psr7\parse_query;

class QueryContains implements Assertion
{

    public function assert(Transaction $actual, Transaction $expected) : void
    {

        if ($actual->request()->getUri()->getQuery() !== '') {
            $actual->request()->assertUriQueryContains(parse_query($expected->request()->getUri()->getQuery()));
        }
    }
}
