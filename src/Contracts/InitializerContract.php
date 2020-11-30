<?php
declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Illuminate\Support\Collection;
use League\Flysystem\Filesystem;

interface InitializerContract
{
    public function __construct(Filesystem $filesystem);
    public function create(string $filename, string $gitRemote): void;
    public function save(string $filename, array $config): void;
    public function discoverGitRemotes(): Collection;
    public function defaultConfig(string $gitRemote): array;
    public function defaultServerConfig(): array;
}
