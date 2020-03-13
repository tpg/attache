<?php

namespace TPG\Attache;

use Symfony\Component\Process\Process;

class Ssh extends Processor
{
    protected Task $task;

    protected const DELIMITER = 'ATTACHE-SCRIPT';

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function run(\Closure $callback = null): int
    {
        $process = $this->getProcess();

        $process->run(function ($type, $output) use ($callback) {
            $callback($this->task, $type, $output);
        });

        return $process->getExitCode();
    }

    protected function getProcess(): Process
    {
        return Process::fromShellCommandline(
            'ssh '.$this->getServerConnectionString()
            ." 'bash -se' << \\".self::DELIMITER.PHP_EOL
            .'('.PHP_EOL
            .'set -e'.PHP_EOL
            .$this->task->script().PHP_EOL
            .')'.PHP_EOL
            .self::DELIMITER
        )->setTimeout(null);
    }

    protected function getServerConnectionString(): string
    {
        $server = $this->task->server();

        return $server->user().'@'.$server->host().' -p'.$server->port();
    }
}
