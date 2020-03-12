<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ServersListCommand
 * @package TPG\Attache\Console
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

        $io->table([], $this->buildTableRows($servers));

        return 0;
    }

    /**
     * Get the rows of server names and hosts.
     *
     * @param array $servers
     * @return array
     */
    protected function buildTableRows(array $servers): array
    {
        $rows = [];

        foreach ($servers as $server) {
            $rows[] = [
                '<info>'.$server['name'].'</info>',
                $server['host'],
            ];
        }

        return $rows;
    }
}
