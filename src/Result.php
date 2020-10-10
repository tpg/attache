<?php

declare(strict_types=1);

namespace TPG\Attache;

use Symfony\Component\Process\Process;

class Result
{
    /**
     * @var Task
     */
    protected Task $task;
    protected $type;
    /**
     * @var null
     */
    protected $value;

    /**
     * TaskResult constructor.
     * @param Task $task
     * @param mixed $type
     * @param mixed $value
     */
    public function __construct(Task $task, $type, $value = null)
    {
        $this->task = $task;
        $this->type = $type;
        $this->value = $value;
    }

    public function isError(): bool
    {
        return $this->type === Process::ERR;
    }

    public function isOutput(): bool
    {
        return $this->type === Process::OUT;
    }

    public function output()
    {
        return $this->value;
    }

    public function task(): Task
    {
        return $this->task;
    }
}
