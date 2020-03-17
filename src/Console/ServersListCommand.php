<?php

namespace TPG\Attache\Console;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Style\SymfonyStyle;
use TPG\Attache\Server;

class ServersListCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure(): void
    {
        $this->setName('servers:list')
            ->setDescription('List configured servers');

        $this->requiresConfig();
    }

    /**
     * Get a list of configured servers.
     *
     * @return int
     */
    protected function fire(): int
    {
        $servers = $this->config->servers();

        return $this->display($servers);
    }

    /**
     * Display the list on the console.
     *
     * @param Collection $servers
     * @return int
     */
    protected function display(Collection $servers): int
    {
        $io = new SymfonyStyle($this->input, $this->output);

        $io->table([
            'Server Name', 'Host',
        ], $servers->map(function (Server $server) {
            return [
                $this->info($server->name()),
                $server->host(),
            ];
        })->toArray());

        return 0;
    }
}
