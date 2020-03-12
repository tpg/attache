<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class ReleasesPruneCommand.
 */
class ReleasesPruneCommand extends SymfonyCommand
{
    use Command;

    /**
     * Prune the releases on the server leaving the active one and the previous one.
     */
    public function configure()
    {
        $this->setName('releases:prune')
            ->setDescription('Prune releases from the specified server. Retains the most recent two')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server');

        $this->requiresConfig();
    }

    /**
     * @return int
     */
    public function fire(): int
    {
        return 0;
    }
}
