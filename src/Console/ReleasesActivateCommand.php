<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class ReleasesActivateCommand
 * @package TPG\Attache\Console
 */
class ReleasesActivateCommand extends SymfonyCommand
{
    use Command;

    /**
     * Activate the specified release.
     */
    protected function configure()
    {
        $this->setName('releases:activate')
            ->setDescription('Activate a release on the specified server')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server')
            ->addArgument('release', InputArgument::REQUIRED, 'The ID of the release to activate');

        $this->requiresConfig();
    }

    /**
     * @return int
     */
    protected function fire(): int
    {
        return 0;
    }
}
