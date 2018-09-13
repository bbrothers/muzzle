<?php

namespace Muzzle\Assertions;

use function Muzzle\is_json;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\Http\Message\StreamInterface;
use function Muzzle\is_regex;

class BodyMatches implements Assertion
{

    private $body;

    /**
     * @param StreamInterface|array|string $body
     */
    public function __construct($body)
    {

        $this->body = $body instanceof StreamInterface ? (string) $body : $body;
    }

    public function __invoke(AssertableRequest $actual, Muzzle $muzzle) : void
    {

        if (! is_json($this->body) and is_regex($this->body)) {
            PHPUnit::assertRegExp($this->body, (string) $actual->getBody());
            return;
        }

        if (! $actual->isJson()) {
            $actual->assertSee(is_array($this->body) ? json_encode($this->body) : (string) $this->body);
            return;
        }

        $decoded = $actual->decode();

        $body = is_string($this->body) ? json_decode($this->body, true) : (array) $this->body;

        $this->assertArrayMatches($body, $decoded);
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
