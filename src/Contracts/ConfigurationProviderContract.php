<?php
declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Illuminate\Support\Collection;
use League\Flysystem\Filesystem;
use TPG\Attache\Server;

interface ConfigurationProviderContract
{
    public function __construct(Filesystem $filesystem);
    public function load(string $filename): void;
    public function setConfig(array $config): void;
    public function configured(): bool;
    public function repository(): string;
    public function servers(): Collection;
    public function server(string $name): Server;
    public function default(): ?string;
    public function defaultServer(): ?Server;
}
