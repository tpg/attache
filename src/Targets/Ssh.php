<?php

declare(strict_types=1);

namespace TPG\Attache\Targets;

use Symfony\Component\Process\Process;
use TPG\Attache\Result;
use TPG\Attache\Server;
use TPG\Attache\Task;

class Ssh extends Target
{
    /**
     * @var Server
     */
    protected Server $server;

    /**
     * Ssh constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function run(Task $task, callable $callback = null): int
    {
        $process = $this->getProcess($task);

        $process->run(function ($type, $data) use ($task, $callback) {
            $callback($type, $data);
        });

        return $process->getExitCode();
    }

    protected function getProcess(Task $task): Process
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
