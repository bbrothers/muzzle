<?php

namespace Muzzle\Assertions;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Muzzle\CliFormatter;
use PHPUnit\Framework\Assert as PHPUnitAssert;
use function Muzzle\is_regex;

class Assert extends PHPUnitAssert
{

    use ArraySubsetAsserts;

    public static function assertArraysMatch(iterable $expected, array $actual) : void
    {

        foreach ($expected as $key => $value) {
            static::assertArrayHasKey(
                $key,
                $actual,
                "Did not find the expected key [{$key}] in the provided content:" . PHP_EOL
                . CliFormatter::format($actual)
            );

            if (is_regex($value)) {
                static::assertIsScalar(
                    $actual[$key],
                    "Cannot match pattern [{$value}] against non-string value:" . PHP_EOL
                    . CliFormatter::format($actual[$key])
                );
                static::assertRegExp($value, $actual[$key]);
                continue;
            }

            if (is_array($value) and is_array($actual[$key])) {
                static::assertArraysMatch($value, $actual[$key]);
                continue;
            }

            static::assertEquals(
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
