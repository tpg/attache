<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use TPG\Attache\Task;

interface TargetContract
{
    public function run(Task $task, callable $callback = null): int;

    public function setTimeout(int $timeout): void;
}
