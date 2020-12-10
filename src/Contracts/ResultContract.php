<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use TPG\Attache\Task;

interface ResultContract
{
    public function __construct(TaskContract $task, bool $success, $value = null);

    public function isSuccess(): bool;

    public function output();

    public function task(): TaskContract;
}
