<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Collection;
use TPG\Attache\Targets\Target;

class TaskRunner
{
    protected const PROCESS_TIMEOUT = 3600;

    /**
     * @var Target
     */
    protected Target $target;

    /**
     * @var Collection
     */
    protected Collection $results;

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

        $this->results = collect();

        collect($tasks)->each(function (Task $task) use ($callback, $tasks, &$count) {
            $count++;

            $progress = $this->percentage(count($tasks), $count);

            $exitCodes[] = $this->target->run($task, function ($type, $output) use ($callback, $task, $progress) {
                $result = new Result($task, $type, $output);

                $this->results->push($result);

                if ($callback) {
                    $callback($result, $progress);
                }
            });
        });

        return $exitCodes;
    }

    public function errors(): Collection
    {
        return $this->results->filter(fn (Result $result) => $result->isError());
    }

    public function hasError(): bool
    {
        return $this->errors()->count() > 0;
    }

    public function getResults(): Collection
    {
        return $this->results;
    }

    protected function percentage(int $total, int $enum): int
    {
        return $enum * 100 / $total;
    }
}
