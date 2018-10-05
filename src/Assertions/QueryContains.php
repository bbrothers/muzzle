<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\AssertableRequest;
use function GuzzleHttp\Psr7\parse_query;

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

        Assert::assertArraysMatch($this->query, $parameters);
    }

    private function aliasAsUnparsedArraySyntax(AssertableRequest $actual, array $parameters) : array
    {

        return array_merge(parse_query($actual->getUri()->getQuery()), $parameters);
    }
}
