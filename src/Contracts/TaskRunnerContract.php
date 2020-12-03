<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Illuminate\Support\Collection;
use TPG\Attache\Targets\Target;

interface TaskRunnerContract
{
    public function __construct(Target $target);
    public function run(array $tasks, callable $callback = null): array;
    public function errors(): Collection;
    public function hasError(): bool;
    public function getResults(): Collection;
}
