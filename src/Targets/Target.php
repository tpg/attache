<?php

declare(strict_types=1);

namespace TPG\Attache\Targets;

use TPG\Attache\Contracts\TargetContract;
use TPG\Attache\Contracts\TaskContract;
use TPG\Attache\Task;

abstract class Target implements TargetContract
{
    protected int $timeout = 3600;

    abstract public function run(TaskContract $task, callable $callback = null): int;

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }
}
