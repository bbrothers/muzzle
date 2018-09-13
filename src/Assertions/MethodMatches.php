<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use PHPUnit\Framework\Assert as PHPUnit;

class MethodMatches implements Assertion
{

    /**
     * @var string
     */
    private $methods;

    public function __construct(string ...$methods)
    {

        $this->methods = array_map('strtoupper', $methods);
    }

    public function __invoke(AssertableRequest $actual, Muzzle $muzzle) : void
    {

        PHPUnit::assertArrayHasKey(
            $actual->getMethod(),
            array_flip(array_map('strtoupper', $this->methods)),
            sprintf(
                'Expected HTTP method [%s]. Got [%s] for request to %s.',
                implode(', ', array_map('strtoupper', $this->methods)),
                $actual->getMethod(),
                urldecode($actual->getUri())
            )
        );
    }

    public function methods() : array
    {
        return $this->methods;
    }
}
