<?php

namespace Muzzle\Assertions;

use Muzzle\CliFormatter;
use Muzzle\Messages\AssertableRequest;

class QueryEquals implements Assertion
{

    /**
     * @var array
     */
    private $query;

    public function __construct(array $query)
    {

        $this->query = $query;
    }

    public function __invoke(AssertableRequest $actual) : void
    {

        $expected = $this->query;
        parse_str($actual->getUri()->getQuery(), $query);
        ksort($query);
        ksort($expected);
        Assert::assertEquals(
            $expected,
            $query,
            'The expected query' . PHP_EOL
            . CliFormatter::format($expected) . PHP_EOL
            . 'does not equal' . PHP_EOL
            . CliFormatter::format($query) . PHP_EOL
        );
    }
}
