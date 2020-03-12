<?php

namespace TPG\Attache\Console;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ServersListCommand.
 */
class ServersListCommand extends SymfonyCommand
{
    use Command;

    /**
     * List the currently configured servers.
     */
    protected function configure(): void
    {
        $this->setName('servers:list')
            ->setDescription('List configured servers');

        $this->requiresConfig();
    }

    /**
     * @return int
     */
    protected function fire(): int
    {
        $servers = $this->config->servers();

        $io = new SymfonyStyle($this->input, $this->output);

        $io->table([], $this->getTableRows($servers));

        return 0;
    }

    /**
     * Get the rows of server names and hosts.
     *
     * @param array $servers
     * @return array
     */
    protected function getTableRows(Collection $servers): array
    {
        $rows = [];

        foreach ($servers as $server) {
            $rows[] = [
                '<info>'.$server->name().'</info>',
                $server->host(),
            ];
        }

        return $rows;
    }
}
