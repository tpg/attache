<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseService;

/**
 * Class DownCommand
 * @package TPG\Attache\Console
 */
class DownCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName('releases:down')
            ->setDescription('Take the specific release offline')
            ->requiresConfig()
            ->requiresServer();
    }

    /**
     * Bring the deployment offline.
     *
     * @return int
     */
    protected function fire(): int
    {
        (new ReleaseService($this->server))->down();

        $this->output->writeln('<info>'.$this->server->name().'</info> is offline');

        return 0;
    }
}
