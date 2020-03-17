<?php

namespace TPG\Attache;

use Symfony\Component\Process\Process;

/**
 * Class Ssh.
 */
class Ssh
{
    /**
     * @var Task
     */
    protected Task $task;

    /**
     * @var bool
     */
    protected bool $tty = false;

    /**
     * @var string
     */
    protected const DELIMITER = 'ATTACHE-SCRIPT';

    /**
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Run the task.
     *
     * @param \Closure|null $callback
     * @return int
     */
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

    /**
     * Force TTY if supported.
     *
     * @return $this
     */
    public function tty(): self
    {
        $this->tty = true;

        return $this;
    }

    /**
     * Get a Process instance.
     *
     * @return Process
     */
    protected function getProcess(): Process
    {
        return Process::fromShellCommandline(
            'ssh '.$this->getServerConnectionString().' '
            .$this->script()
        )->setTimeout(null);
    }

    /**
     * Get the compiled task script.
     *
     * @return string
     */
    public function script(): string
    {
        return "'bash -se' << \\".self::DELIMITER.PHP_EOL
            .'('.PHP_EOL
            .'set -e'.PHP_EOL
            .$this->task->script().PHP_EOL
            .')'.PHP_EOL
            .self::DELIMITER;
    }

    /**
     * Get the SSH connection string from the Server instance.
     *
     * @return string
     */
    protected function getServerConnectionString(): string
    {
        $server = $this->task->server();

        return $server->sshConnectionString();
    }
}
