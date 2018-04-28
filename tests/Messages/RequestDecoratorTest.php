<?php

namespace Muzzle\Messages;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestDecoratorTest extends TestCase
{

    use TestsDecorators;

    /** @test */
    public function itDelegatesInterfaceMethodsToTheDecoratedRequest()
    {

        $mock = $this->mockInterface(RequestInterface::class);

        $decorated = new class($mock->reveal()) implements RequestInterface
        {

            use RequestDecorator;
        };

        $this->assertInterfaceMethodsAreDelegated(RequestInterface::class, $decorated, $mock);
    }
}
