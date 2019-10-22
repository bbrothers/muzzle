<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\AssertableRequest;
use Psr\Http\Message\StreamInterface;
use function Muzzle\is_json;
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

    public function __invoke(AssertableRequest $actual) : void
    {

        if (! is_json($this->body) and is_regex($this->body)) {
            Assert::assertRegExp($this->body, (string) $actual->getBody());
            return;
        }

        if (! $actual->isJson()) {
            $actual->assertSee(is_array($this->body) ? json_encode($this->body) : (string) $this->body);
            return;
        }

        $decoded = $actual->decode();

        $body = is_string($this->body) ? json_decode($this->body, true) : (array) $this->body;

        Assert::assertArraysMatch($body, $decoded);
    }
}
