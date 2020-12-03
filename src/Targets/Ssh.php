<?php

declare(strict_types=1);

namespace TPG\Attache\Targets;

use Symfony\Component\Process\Process;
use TPG\Attache\Contracts\ServerContract;
use TPG\Attache\Contracts\TaskContract;
use TPG\Attache\Server;

class Ssh extends Target
{
    protected ServerContract $server;

    public function __construct(ServerContract $server)
    {
        $this->server = $server;
    }

    public function server(): ServerContract
    {
        return $this->server;
    }

    public function run(TaskContract $task, callable $callback = null): int
    {
        $process = $this->getProcess($task);

        $process->run(function ($type, $data) use ($callback) {
            $callback($type, $data);
        });

        return $process->getExitCode();
    }

    protected function getProcess(TaskContract $task): Process
    {
        return Process::fromShellCommandline(
            $this->sshConnectionString()
            .' '
            .$task->bashScript()
        )->setTimeout($this->timeout);
    }

    protected function sshConnectionString(): string
    {
        return 'ssh '.$this->server->connectionString();
    }
}
