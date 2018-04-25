<?php

namespace Muzzle\Assertions;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Muzzle\Messages\Transaction;
use Muzzle\Muzzle;

class UriPathMatches implements Assertion
{

    /**
     * @var Muzzle
     */
    private $muzzle;

    public function __construct(Muzzle $muzzle)
    {

        $this->muzzle = $muzzle;
    }

    public function assert(Transaction $actual, Transaction $expected) : void
    {

        $actual->request()->assertUriPath(
            UriResolver::resolve(
                $this->muzzle->getConfig('base_uri') ?: new Uri,
                $expected->request()->getUri()
            )->getPath()
        );
    }
}
