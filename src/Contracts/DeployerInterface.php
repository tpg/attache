<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Closure;
use League\Flysystem\Filesystem;

interface DeployerInterface
{
    public function __construct(Filesystem $filesystem, ServerInterface $server);

    public function deploy(string $releaseId, Closure $callback): bool;
}
