<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

interface InitializerInterface
{
    public function create(array $config = null, string $filename = '.attache.json'): void;
    public function discoverGitRemotes(): array;
    public function config(string $remote): array;
}
