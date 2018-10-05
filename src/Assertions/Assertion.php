<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\AssertableRequest;

interface Assertion
{

    public function __invoke(AssertableRequest $actual) : void;
}
