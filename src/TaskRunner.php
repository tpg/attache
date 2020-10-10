<?php

declare(strict_types=1);

namespace TPG\Attache;

use TPG\Attache\Targets\Target;

class TaskRunner
{
    protected const PROCESS_TIMEOUT = 3600;

    /**
     * @var Target
     */
    protected Target $target;

    /**
     * TaskRunner constructor.
     * @param Target $target
     */
    public function __construct(Target $target)
    {
        $this->target = $target;
    }

    /**
     * @param Task[] $tasks
     * @param callable|null $callback
     */
    public function run(array $tasks, callable $callback = null)
    {
        collect($tasks)->each(function (Task $task) use ($callback) {
            $this->target->run($task, $callback);
        });
    }
}
