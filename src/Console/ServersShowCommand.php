<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Server;

/**
 * Class ServersShowCommand.
 */
class ServersShowCommand extends SymfonyCommand
{
    use Command;

    /**
     * Show the configuration information for the specified server.
     */
    protected function configure(): void
    {
        $this->setName('servers:show')
            ->setDescription('Show details of the specified server')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server');

        $this->requiresConfig();
    }

    /**
     * @return int
     * @throws ConfigurationException
     */
    protected function fire(): int
    {
        $server = $this->config->server($this->argument('server'));

        $this->showTable($server);

        return 0;
    }

    /**
     * Show a table on the console with the server details.
     *
     * @param Server $server
     */
    protected function showTable(Server $server): void
    {
        $io = new SymfonyStyle($this->input, $this->output);

        $this->output->writeln(json_encode($server->config(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, 512));
    }
}
