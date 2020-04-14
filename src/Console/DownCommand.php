<?php

namespace TPG\Attache\Console;

use TPG\Attache\ReleaseService;

class DownCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure(): void
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
