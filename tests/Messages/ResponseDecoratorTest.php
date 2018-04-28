<?php

namespace Muzzle\Messages;

use BadMethodCallException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseDecoratorTest extends TestCase
{

    use TestsDecorators;

    /** @test */
    public function itDelegatesInterfaceMethodsToTheDecoratedResponse()
    {

        $mock = $this->mockInterface(ResponseInterface::class);

        $decorated = new class($mock->reveal()) implements ResponseInterface
        {

            use ResponseDecorator;
        };

        $this->assertInterfaceMethodsAreDelegated(ResponseInterface::class, $decorated, $mock);
    }

    /** @test */
    public function itCallsNonInterfaceMethodsOnTheDecoratedClass()
    {

        $response = new class extends Response {

            public $property = 'foo';

            public function nonInterfaceMethod()
            {

                return true;
            }

            public function withMethodCheck()
            {

                return $this;
            }
        };

        $decorated = new class($response) implements ResponseInterface
        {

            use ResponseDecorator;
        };

        $this->assertTrue(isset($decorated->property));
        $this->assertSame('foo', $decorated->property);
        $this->assertNull($decorated->undefined);
        $this->assertTrue($decorated->nonInterfaceMethod());
        $this->assertInstanceOf(get_class($decorated), $decorated->withMethodCheck());
    }

    /** @test */
    public function itThrowsAnExceptionIfACalledMethodIsNotDefinedOnTheDelegate()
    {

        $decorated = new class(new Response) implements ResponseInterface
        {

            use ResponseDecorator;
        };

        $this->expectException(BadMethodCallException::class);
        $decorated->undefinedMethod();
    }
}
