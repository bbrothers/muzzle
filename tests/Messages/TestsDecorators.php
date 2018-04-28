<?php

namespace Muzzle\Messages;

use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

trait TestsDecorators
{

    protected $methods = [];

    protected function assertInterfaceMethodsAreDelegated(
        string $interface,
        $decorated,
        ProphecyInterface $mock
    ) : void {

        foreach ($this->interfaceMethods($interface) as $method) {
            $decorated->{$method->name}(...$this->mockParameter($method));

            $arguments = $this->expectedArguments($method);
            $mock->{$method->name}(...$arguments)->shouldHaveBeenCalled();
        }
    }

    protected function expectedArguments(ReflectionMethod $method) : array
    {

        return array_map(function (ReflectionParameter $parameter) {

            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            if ($parameter->hasType()) {
                return Argument::type($parameter->getType()->getName());
            }

            return Argument::any();
        }, $method->getParameters());
    }

    protected function mockInterface(string $interface) : ProphecyInterface
    {

        $methods = $this->interfaceMethods($interface);

        $double = $this->prophesize($interface);
        foreach ($methods as $method) {
            $arguments = $this->expectedArguments($method);
            $double->{$method->name}(...$arguments)->willReturn($double);
        }

        return $double;
    }

    /**
     * @param string $interface
     * @return array|ReflectionMethod[]
     * @throws \ReflectionException
     */
    protected function interfaceMethods(string $interface) : array
    {

        if (! isset($this->methods[$interface])) {
            $this->methods[$interface] = array_filter(
                (new ReflectionClass($interface))->getMethods(),
                function (ReflectionMethod $method) {

                    return ! $method->isStatic();
                }
            );
        }

        return $this->methods[$interface];
    }

    protected function mockParameter(ReflectionMethod $method) : array
    {

        return array_map(function (ReflectionParameter $parameter) {

            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            if ($parameter->hasType()) {
                return ($this->prophesize($parameter->getType()->getName()))->reveal();
            }

            return $parameter->name;
        }, $method->getParameters());
    }
}
