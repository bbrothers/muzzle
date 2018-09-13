<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use PHPUnit\Framework\Assert as PHPUnit;
use function Muzzle\is_regex;

class QueryContains implements Assertion
{

    /**
     * @var array
     */
    private $query;

    public function __construct(array $query)
    {

        $this->query = $query;
    }

    public function __invoke(AssertableRequest $actual, Muzzle $muzzle) : void
    {

        parse_str($actual->getUri()->getQuery(), $parameters);

        $this->assertArrayMatches($this->query, $parameters);
    }

    /**
     * @param $body
     * @param $decoded
     */
    private function assertArrayMatches($body, $decoded) : void
    {

        foreach ($body as $key => $value) {
            PHPUnit::assertArrayHasKey(
                $key,
                $decoded,
                "The body does not contain contain the expected key [{$key}]."
            );

            if (is_regex($value)) {
                PHPUnit::assertRegExp($value, $decoded[$key]);
                continue;
            }

            if (is_array($value) and is_array($decoded[$key])) {
                $this->assertArrayMatches($value, $decoded[$key]);
                continue;
            }

            PHPUnit::assertEquals($value, $decoded[$key]);
        }
    }
}
