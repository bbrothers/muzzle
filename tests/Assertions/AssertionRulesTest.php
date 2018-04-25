<?php

namespace Muzzle\Assertions;

use InvalidArgumentException;
use Muzzle\Messages\Transaction;
use PHPUnit\Framework\TestCase;

/**
 * @backupStaticAttributes enabled
 */
class AssertionRulesTest extends TestCase
{

    /** @test */
    public function itCanPushAnAssertionOnToTheRulesStack()
    {

        $assertion = $this->ruleStub();

        AssertionRules::push(get_class($assertion));

        $assertions = AssertionRules::assertions();
        $this->assertEquals(get_class($assertion), end($assertions));
    }

    /** @test */
    public function itCanUnshiftAnAssertionToTheStartOfTheRulesStack()
    {

        $assertion = $this->ruleStub();

        AssertionRules::unshift(get_class($assertion));

        $assertions = AssertionRules::assertions();
        $this->assertEquals(get_class($assertion), reset($assertions));
    }

    /** @test */
    public function itCanOverrideTheListOfAssertionRules()
    {

        AssertionRules::setAssertions(get_class($this->ruleStub()), get_class($this->ruleStub()));

        $this->assertCount(2, AssertionRules::assertions());
    }

    /** @test */
    public function itThrowsAnExceptionIfARuleDoesNotImplementAssertion()
    {

        $this->expectException(InvalidArgumentException::class);

        AssertionRules::setAssertions(get_class(new class {}));
    }

    /**
     * @return Assertion
     */
    private function ruleStub() : Assertion
    {

        return new class implements Assertion
        {

            public function assert(Transaction $actual, Transaction $expected) : void
            {
            }
        };
    }
}
