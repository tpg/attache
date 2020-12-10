<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Collection;
use TPG\Attache\Contracts\ResultContract;
use TPG\Attache\Contracts\TargetContract;
use TPG\Attache\Contracts\TaskContract;
use TPG\Attache\Contracts\TaskRunnerContract;

class TaskRunner implements TaskRunnerContract
{
    protected const PROCESS_TIMEOUT = 3600;

    protected TargetContract $target;

    protected Collection $results;

    public function __construct(TargetContract $target)
    {
        $this->target = $target;
    }

    /**
     * @param TaskContract[] $tasks
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

            $exitCodes[] = $this->target->run($task, function ($success, $output) use ($callback, $task, $progress) {
                $result = new Result($task, $success, $output);

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
        return $this->results->filter(fn (ResultContract $result) => ! $result->isSuccess());
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
