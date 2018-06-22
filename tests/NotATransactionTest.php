<?php

namespace Muzzle;

use PHPUnit\Framework\TestCase;

class NotATransactionTest extends TestCase
{

    /**
     * @test
     * @dataProvider variableTypes
     * @param mixed $value
     * @param string $type
     */
    public function itWillReturnAThrowableInstanceWithAMessageDescribingTheProvidedType($value, $type)
    {

        $this->assertRegExp("/.*{$type}$/", NotATransaction::value($value)->getMessage());
    }

    public function variableTypes() : \Generator
    {

        yield from [
            [['foo'], 'array'],
            [new \DateTime, \DateTime::class],
            [true, 'boolean'],
            ['foo', 'string'],
            [5, 'integer'],
            [3.14, 'double'],
        ];
    }
}
