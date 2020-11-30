<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Illuminate\Support\Collection;
use TPG\Attache\Targets\Target;
use TPG\Attache\TaskRunner;

interface ReleaseManagerContract
{
    public function setTarget(Target $target): void;

    public function setTaskRunner(TaskRunner $runner): void;

    public function list(): Collection;

    public function active(): string;

    public function activate(string $release): bool;
}
