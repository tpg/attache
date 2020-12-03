<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

interface ServerContract
{
    public function __construct(string $name = null, array $config = []);
    public function setName(string $name): void;
    public function setConfig(array $config): void;
    public function name(): string;
    public function host(): string;
    public function port(): int;
    public function user(): string;
    public function branch(): string;
    public function root(bool $trailingSlash = false): string;
    public function paths(bool $absolute = true): array;
    public function path(string $key, bool $absolute = true): ?string;
    public function migrate(): bool;
    public function php(): string;
    public function composer(): string;
    public function assets(string $key = null);
    public function connectionString(): string;
    public function config($key, $default = null);
}
