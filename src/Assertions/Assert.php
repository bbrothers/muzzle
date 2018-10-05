<?php

namespace Muzzle\Assertions;

use Muzzle\CliFormatter;
use PHPUnit\Framework\Assert as PHPUnit;
use function Muzzle\is_regex;

class Assert
{

    public static function assertArraysMatch(iterable $expected, array $actual) : void
    {

        foreach ($expected as $key => $value) {
            PHPUnit::assertArrayHasKey(
                $key,
                $actual,
                "The array does not contain contain the expected key [{$key}]."
            );

            if (is_regex($value)) {
                PHPUnit::assertRegExp($value, $actual[$key]);
                continue;
            }

            if (is_array($value) and is_array($actual[$key])) {
                static::assertArraysMatch($value, $actual[$key]);
                continue;
            }

            PHPUnit::assertEquals(
                $value,
                $actual[$key],
                "The expected value for [{$key}]" . PHP_EOL
                . CliFormatter::format($value) . PHP_EOL
                . 'does not equal' . PHP_EOL
                . CliFormatter::format($actual[$key]) . PHP_EOL
            );
        }
    }
}
