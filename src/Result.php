<?php

declare(strict_types=1);

namespace TPG\Attache;

use TPG\Attache\Contracts\ResultContract;
use TPG\Attache\Contracts\TaskContract;

class Result implements ResultContract
{
    protected TaskContract $task;
    protected bool $success;
    protected $value;

    public function __construct(TaskContract $task, bool $success, $value = null)
    {
        $this->task = $task;
        $this->success = $success;
        $this->value = $value;
    }

    public function isSuccess(): bool
    {
        return $this->success;
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
