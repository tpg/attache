<?php

namespace TPG\Attache;

abstract class Processor
{
    abstract public function run(\Closure $callback = null): int;
}
