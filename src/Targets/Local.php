<?php

declare(strict_types=1);

namespace TPG\Attache\Targets;

use Symfony\Component\Process\Process;
use TPG\Attache\Contracts\TaskContract;
use TPG\Attache\Task;

class Local extends Target
{
    protected string $path;

    /**
     * Local constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function run(TaskContract $task, callable $callback = null): int
    {
        $process = Process::fromShellCommandline($task->bashScript())
            ->setTimeout($this->timeout);

        $process->run(function ($type, $output) use ($callback) {
            if ($callback) {
                $callback($type, $output);
            }
        });

        return $process->getExitCode();
    }
}
