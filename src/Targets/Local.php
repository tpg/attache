<?php

declare(strict_types=1);

namespace TPG\Attache\Targets;

use Symfony\Component\Process\Process;
use TPG\Attache\Targets\Target;
use TPG\Attache\Task;
use TPG\Attache\Result;

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

    public function run(Task $task, callable $callback = null): int
    {
        $process = Process::fromShellCommandline($task->bashScript())
            ->setTimeout($this->timeout);

        $process->run(function ($type, $output) use ($task, $callback) {
            if ($callback) {
                $callback(new Result($task, $type, $output));
            }
        });

        return $process->getExitCode();
    }
}
