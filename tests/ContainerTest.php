<?php

namespace Muzzle;

use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{

    /** @test */
    public function itCanPushMuzzleInstancesOnToTheStack()
    {

        $container = new Container;
        $this->assertCount(0, $container);
        $container->push(new Muzzle);
        $this->assertCount(1, $container);
    }

    /** @test */
    public function itCanPullEachInstanceOffTheStackAndRunTheAssertions()
    {

        $container = new Container;
        $muzzles = array_map(function () use ($container) {

            $muzzle = $this->prophesize(Muzzle::class);
            $container->push($muzzle->reveal());
            return $muzzle;
        }, array_fill(0, 3, null));

        $container->makeAssertions();

        foreach ($muzzles as $muzzle) {
            $muzzle->makeAssertions()->shouldHaveBeenCalled();
        }
        $this->assertCount(0, $container);
    }

    /** @test */
    public function itCanFlushTheContainer()
    {

        $container = new Container;
        $muzzles = array_map(function () use ($container) {

            $muzzle = $this->prophesize(Muzzle::class);
            $container->push($muzzle->reveal());
            return $muzzle;
        }, array_fill(0, 3, null));

        $this->assertCount(3, $container);

        $container->flush();
        $this->assertCount(0, $container);

        foreach ($muzzles as $muzzle) {
            $muzzle->makeAssertions()->shouldNotHaveBeenCalled();
        }
    }
}
