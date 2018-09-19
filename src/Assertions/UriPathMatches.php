<?php

namespace Muzzle\Assertions;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Str;
use function Muzzle\is_regex;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use PHPUnit\Framework\Assert as PHPUnit;

class UriPathMatches implements Assertion
{

    private $uri;

    public function __construct($uri)
    {

        $this->uri = $uri;
    }

    public function __invoke(AssertableRequest $actual, Muzzle $muzzle = null) : void
    {

        $expectedPath = $this->uri($muzzle);

        if (is_regex($this->uri)) {
            $this->assertMatchesPattern($actual, $this->uri);
            return;
        }

        PHPUnit::assertTrue(
            Str::is($expectedPath, $actual->getUri()->getPath()),
            sprintf(
                'The request path [%s] does not match the expectation [%s].',
                urldecode($actual->getUri()->getPath()),
                $expectedPath
            )
        );
    }

    public function uri(Muzzle $muzzle) : string
    {

        return UriResolver::resolve(
            $muzzle->getConfig('base_uri') ?: new Uri,
            new Uri($this->uri)
        )->getPath();
    }

    private function assertMatchesPattern(AssertableRequest $actual, string $expectedPath) : void
    {

        PHPUnit::assertRegExp(
            $expectedPath,
            $actual->getUri()->getPath(),
            sprintf(
                'The request path [%s] does not match the expected pattern [%s].',
                urldecode($actual->getUri()->getPath()),
                $expectedPath
            )
        );
    }
}
