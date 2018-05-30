<?php

namespace Muzzle\PHPUnit;

use Muzzle\Muzzle;

/**
 * @codeCoverageIgnore
 */
trait MuzzleIntegration
{

    protected $muzzleIsOpen;

    protected function assertPostConditions()
    {

        $this->closeMuzzle();

        parent::assertPostConditions();
    }

    protected function closeMuzzle()
    {

        Muzzle::close();
        $this->muzzleIsOpen = false;
    }

    /**
     * @before
     */
    protected function startMuzzle()
    {

        $this->muzzleIsOpen = true;
    }

    /**
     * @after
     */
    protected function purgeMockeryContainer()
    {

        if ($this->muzzleIsOpen) {
            $this->closeMuzzle();
        }
    }
}
