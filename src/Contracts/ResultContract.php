<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use TPG\Attache\Task;

interface ResultContract
{
    public function __construct(TaskContract $task, $type, $value = null);

    public function isError(): bool;

    public function isOutput(): bool;

    public function output();

    public function task(): TaskContract;
}
