<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class ReleasesRollbackCommand
 * @package TPG\Attache\Console
 */
class ReleasesRollbackCommand extends SymfonyCommand
{
    use Command;

    /**
     * Rollback the current release to the previous one.
     */
    protected function configure(): void
    {
        $this->setName('releases:rollback')
            ->setDescription('Rollback to the previous release on the specified server')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server');

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
