<?php

namespace TPG\Attache;

use Symfony\Component\Process\Process;
use TPG\Attache\Exceptions\ProcessException;

class Ssh
{
    /**
     * @var Task
     */
    protected ?Task $task = null;

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
    public function __construct(Task $task = null)
    {
        if ($task) {
            $this->setTask($task);
        }
    }

    public function setTask(Task $task): void
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
        if (! $this->task) {
            throw new \RuntimeException('No task specified');
        }

        if (! $this->task->server()) {
            throw new \RuntimeException('No server to connect to');
        }

        $process = $this->getProcess();

        if ($this->tty) {
            $process->setTty(Process::isTtySupported());
        }

        $process->run(function ($type, $data) use ($callback) {
            if ($type === Process::ERR) {
                throw new ProcessException($data);
            }

            if ($callback) {
                $callback($this->task, $data);
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
        return $this->task->getBashScript(true);
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
