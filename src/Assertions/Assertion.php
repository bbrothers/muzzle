<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;

interface Assertion
{

    public function __invoke(AssertableRequest $actual, Muzzle $muzzle) : void;
}
