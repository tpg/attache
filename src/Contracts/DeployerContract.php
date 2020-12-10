<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

interface DeployerContract
{
    public function deploy(string $releaseId, callable $callback = null): void;

    public function install(string $releaseId, string $env, callable $callback = null): void;
}
