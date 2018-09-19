<?php

namespace Muzzle\Assertions;

use Muzzle\CliFormatter;
use Muzzle\Messages\AssertableRequest;
use PHPUnit\Framework\Assert as PHPUnit;
use function GuzzleHttp\Psr7\parse_query;
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

    public function __invoke(AssertableRequest $actual) : void
    {

        parse_str($actual->getUri()->getQuery(), $parameters);
        // Alias parameters to allow for foo[bar][baz] query key structure.
        $parameters = $this->aliasAsUnparsedArraySyntax($actual, $parameters);

        $this->assertArrayMatches($this->query, $parameters);
    }

    /**
     * @param $query
     * @param $decoded
     */
    private function assertArrayMatches($query, $decoded) : void
    {

        foreach ($query as $key => $value) {
            PHPUnit::assertArrayHasKey(
                $key,
                $decoded,
                "The query does not contain contain the expected key [{$key}]."
            );

            if (is_regex($value)) {
                PHPUnit::assertRegExp($value, $decoded[$key]);
                continue;
            }

            if (is_array($value) and is_array($decoded[$key])) {
                $this->assertArrayMatches($value, $decoded[$key]);
                continue;
            }

            PHPUnit::assertEquals(
                $value,
                $decoded[$key],
                "The expected value for [{$key}]" . PHP_EOL
                . CliFormatter::format($value) . PHP_EOL
                . 'does not equal' . PHP_EOL
                . CliFormatter::format($decoded[$key]) . PHP_EOL
            );
        }
    }

    private function aliasAsUnparsedArraySyntax(AssertableRequest $actual, array $parameters) : array
    {

        return array_merge(parse_query(urldecode($actual->getUri()->getQuery())), $parameters);
    }
}
