<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Closure;
use TPG\Attache\Release;

interface ReleaseManagerInterface
{
    public function __construct(ServerInterface $server);

    public function fetch(): Release;

    public function activate(string $releaseId): string;

    public function prune(int $retain = 2, ?Closure $closure = null): void;
}
