<?php

namespace Muzzle\Assertions;

use Muzzle\CliFormatter;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\Constraint\IsType;
use function Muzzle\is_regex;

class Assert
{

    public static function assertArraysMatch(iterable $expected, array $actual) : void
    {

        foreach ($expected as $key => $value) {
            PHPUnit::assertArrayHasKey(
                $key,
                $actual,
                "Did not find the expected key [{$key}] in the provided content:" . PHP_EOL
                . CliFormatter::format($actual)
            );

            if (is_regex($value)) {
                PHPUnit::assertInternalType(
                    IsType::TYPE_SCALAR,
                    $actual[$key],
                    "Cannot match pattern [{$value}] against non-string value:" . PHP_EOL
                    . CliFormatter::format($actual[$key])
                );
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
