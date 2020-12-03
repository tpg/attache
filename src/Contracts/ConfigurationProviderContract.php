<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Illuminate\Support\Collection;
use League\Flysystem\FilesystemInterface;

interface ConfigurationProviderContract
{
    public function __construct(FilesystemInterface $filesystem);

    public function load(string $filename): void;

    public function setConfig(array $config): void;

    public function configured(): bool;

    public function repository(): string;

    public function servers(): Collection;

    public function server(string $name): ServerContract;

    public function default(): ?string;

    public function defaultServer(): ?ServerContract;
}
