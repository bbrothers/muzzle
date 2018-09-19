<?php

namespace Muzzle\Assertions;

use Illuminate\Support\Arr;
use Muzzle\Messages\AssertableRequest;

class HeadersMatch implements Assertion
{

    /**
     * @var array
     */
    private $headers;

    public function __construct(array $headers)
    {

        $this->headers = $headers;
    }

    public function __invoke(AssertableRequest $actual) : void
    {

        foreach ($this->headers as $header => $value) {
            if (! Arr::isAssoc([$header => $value])) {
                $header = $value;
                $value = null;
            }
            $actual->assertHeader($header, $value);
        }
    }
}
