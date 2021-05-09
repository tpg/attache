<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

interface ServerInterface
{
    public function __construct(string $name, array $config);

    public function settings(string $key): mixed;

    public function setConnection(array $connection): self;

    public function setGit(array $git): self;

    public function setPaths(array $paths): self;

    public function setPhp(array $php): self;

    public function setAssets(array $assets): self;

    public function assets(): array;

    public function name(): string;

    public function rootPath(bool $trailingSlash = false): string;

    public function path(string $key, bool $absolute = true): string;

    public function phpBin(): string;

    public function composerBin(): string;

    public function hostString(): string;

    public function connectionString(): string;

    public function username(): string;

    public function host(): string;

    public function port(): int;

    public function cloneString(): string;
}
