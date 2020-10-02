<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Style\SymfonyStyle;

class ServerListCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('servers:list')
            ->setDescription('List configured servers');

        $this->requiresConfig();
    }

    protected function fire(): int
    {
        $this->loadConfig();

        $servers = $this->configurationProvider->servers();

        $io = new SymfonyStyle($this->input, $this->output);
        $io->table([
            'Server Name',
            'Host',
        ], $servers->map(function ($server) {
            return [
                $server->name(),
                $server->host(),
            ];
        })->toArray());

        return 0;
    }
}
