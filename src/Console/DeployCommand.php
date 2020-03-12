<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Exceptions\ConfigurationException;

/**
 * Class DeployCommand
 * @package TPG\Attache\Console
 */
class DeployCommand extends SymfonyCommand
{
    use Command;

    /**
     * Deploy a new release to the specified server.
     */
    protected function configure(): void
    {
        $this->setName('deploy')
            ->setDescription('Run a deployment to the configured server')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server')
            ->addOption('prune', 'p', InputOption::VALUE_NONE, 'Prune old releases');

        $this->requiresConfig();
    }

    /**
     * @return int
     * @throws ConfigurationException
     */
    protected function fire(): int
    {
        $server = $this->config->server($this->argument('server'));

        return 0;
    }
}
