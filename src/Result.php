<?php

declare(strict_types=1);

namespace TPG\Attache;

use Symfony\Component\Process\Process;
use TPG\Attache\Contracts\ResultContract;
use TPG\Attache\Contracts\TaskContract;

class Result implements ResultContract
{
    protected TaskContract $task;
    protected $type;
    protected $value;

    public function __construct(TaskContract $task, $type, $value = null)
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

    public function task(): TaskContract
    {
        return $this->task;
    }
}
