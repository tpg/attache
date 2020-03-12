<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

/**
 * Class SshCommand
 * @package TPG\Attache\Console
 */
class SshCommand extends SymfonyCommand
{
    use Command;

    /**
     * Open an SSH connection to the specified server.
     */
    protected function configure(): void
    {
        $this->setName('ssh')
            ->setDescription('Open an SSH connection to the specified server')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server');

        $this->requiresConfig();
    }

    /**
     * @return int
     */
    protected function fire(): int
    {
        $connection = $this->config->serverConnectionString($this->argument('server'));

        passthru('ssh '.$connection);

        return 0;
    }
}
