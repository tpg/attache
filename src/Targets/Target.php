<?php

declare(strict_types=1);

namespace TPG\Attache\Targets;

use TPG\Attache\Task;

abstract class Target
{
    protected int $timeout = 3600;

    abstract public function run(Task $task, callable $callback = null): int;

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }
}
