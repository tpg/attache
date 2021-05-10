<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Illuminate\Support\Collection;
use TPG\Attache\Server;

interface ConfigurationProviderInterface
{
    public function loadConfigFile(string $filename): void;

    public function loadConfig(array | string $config): void;

    public function serverNames(): array;

    public function servers(): Collection;

    public function server(string $name): Server;

    public function defaultServer(): ?Server;
}
