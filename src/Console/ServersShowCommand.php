<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use TPG\Attache\Exceptions\ConfigurationException;

/**
 * Class ServersShowCommand
 * @package TPG\Attache\Console
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
     * @param array $server
     */
    protected function showTable(array $server): void
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $io->table([
            'Server',
            $server['name'],
        ], [
            ['<info>Host</info>', $server['host']],
            ['<info>Port</info>', $server['port']],
            ['<info>User</info>', $server['user']],
            ['<info>Root</info>', $server['root']],
            ['<info>Branch</info>', $server['branch']],
        ]);
    }
}
