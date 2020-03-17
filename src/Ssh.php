<?php

namespace TPG\Attache;

use Symfony\Component\Process\Process;

class Ssh extends Processor
{
    protected Task $task;

    protected bool $tty = false;

    protected const DELIMITER = 'ATTACHE-SCRIPT';

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function run(\Closure $callback = null): int
    {
        $process = $this->getProcess();

        if ($this->tty) {
            $process->setTty(Process::isTtySupported());
        }

        $process->run(function ($type, $output) use ($callback) {
            if ($callback) {
                $callback($this->task, $type, $output);
            }
        });

        return $process->getExitCode();
    }

    public function tty(): self
    {
        $this->tty = true;

        return $this;
    }

    protected function getProcess(): Process
    {
        return Process::fromShellCommandline(
            'ssh '.$this->getServerConnectionString().' '
            .$this->script()
        )->setTimeout(null);
    }

    public function script(): string
    {
        return "'bash -se' << \\".self::DELIMITER.PHP_EOL
            .'('.PHP_EOL
            .'set -e'.PHP_EOL
            .$this->task->script().PHP_EOL
            .')'.PHP_EOL
            .self::DELIMITER;
    }

    protected function getServerConnectionString(): string
    {
        $server = $this->task->server();

        return $server->user().'@'.$server->host().' -p'.$server->port();
    }
}
