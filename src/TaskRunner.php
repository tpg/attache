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
     *
     * @return int[]
     */
    public function run(array $tasks, callable $callback = null): array
    {
        $exitCodes = [];

        $count = 0;

        collect($tasks)->each(function (Task $task) use ($callback, $tasks, &$count) {
            $count++;

            $progress = $this->percentage(count($tasks), $count);

            $exitCodes[] = $this->target->run($task, function ($type, $output) use ($callback, $task, $progress) {
                $callback(new Result($task, $type, $output), $progress);
            });
        });

        return $exitCodes;
    }

    protected function percentage(int $total, int $enum): int
    {
        return $enum * 100 / $total;
    }
}
