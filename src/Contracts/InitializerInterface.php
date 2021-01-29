<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

interface InitializerInterface
{
    public function create(array $config, string $filename = '.attache.json');

    public function discoverGitRemotes(): array;

    public function config(string $remote): array;
}
