<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseService;

/**
 * Class UpCommand
 * @package TPG\Attache\Console
 */
class UpCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName('releases:up')
            ->setDescription('Put the specific release online')
            ->requiresConfig()
            ->requiresServer();
    }

    /**
     * Bring the active deployment offline.
     *
     * @return int
     */
    protected function fire(): int
    {
        (new ReleaseService($this->server))->up();

        $this->output->writeln('<info>'.$this->server->name().'</info> is online');

        return 0;
    }
}
