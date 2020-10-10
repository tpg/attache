<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

class ReleaseListCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('release:list')
            ->setDescription('Get a list of available releases for the specified server')
            ->requiresConfig()
            ->requiresServer();
    }

    protected function fire(): int
    {
        return 0;
    }
}
