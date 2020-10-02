<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

class ReleasesListCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('releases:list')
            ->setDescription('Get a list of available releases for the specified server')
            ->requiresConfig()
            ->requiresServer();
    }

    protected function fire(): int
    {
        dd($this->configurationProvider->servers()->first());

        return 0;
    }
}
