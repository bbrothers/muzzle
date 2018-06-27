<?php

namespace Muzzle;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class CliFormatter
{

    protected static $instance;
    protected $dumper;
    protected $cloner;

    private function __construct()
    {

        $this->dumper = new CliDumper(null, null, CliDumper::DUMP_LIGHT_ARRAY);
        $this->cloner = new VarCloner;
    }

    public static function format(...$arguments) : ?string
    {

        if (! self::$instance) {
            self::$instance = new static;
        }

        return self::$instance->dumper()->dump(self::$instance->cloner()->cloneVar($arguments), true);
    }

    private function dumper() : CliDumper
    {

        return $this->dumper;
    }

    private function cloner() : VarCloner
    {

        return $this->cloner;
    }
}
