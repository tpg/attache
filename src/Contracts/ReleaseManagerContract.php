<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Illuminate\Support\Collection;
use TPG\Attache\Targets\Target;

interface ReleaseManagerContract
{
    public function setTarget(Target $target): void;

    public function setTaskRunner(TaskRunnerContract $runner): void;

    public function hasInstallation(): bool;

    public function list(): Collection;

    public function active(): string;

    public function activate(string $release): bool;
}
