<?php

namespace Muzzle\Assertions;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class ArrayMatchesTest extends TestCase
{

    /** @test */
    public function itAssertsThatTheActualHasTheExpectedKey() : void
    {

        $expected = ['foo' => 'bar'];

        Assert::assertArraysMatch($expected, ['foo' => 'bar']);

        $this->expectException(ExpectationFailedException::class);
        Assert::assertArraysMatch($expected, ['bar' => 'baz']);
    }

    /** @test */
    public function itMatchesArrayValuesToARegexPattern() : void
    {

        $expected = ['foo' => '#^b.+[rz]$#'];

        Assert::assertArraysMatch($expected, ['foo' => 'bar']);
        Assert::assertArraysMatch($expected, ['foo' => 'boz']);

        $this->expectException(ExpectationFailedException::class);
        Assert::assertArraysMatch($expected, ['bar' => 'box']);
    }

    /** @test */
    public function itWillRecursivelyCompareArrays() : void
    {

        $expected = ['foo' => ['bar' => ['baz' => 'qux']]];

        Assert::assertArraysMatch(
            $expected,
            ['foo' => ['bar' => ['baz' => 'qux']]]
        );

        $this->expectException(ExpectationFailedException::class);
        Assert::assertArraysMatch(
            $expected,
            ['foo' => ['bar' => ['baz' => 'foo']]]
        );
    }
}
