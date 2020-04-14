<?php

namespace TPG\Attache\Console;

use TPG\Attache\ReleaseService;

class UpCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure(): void
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
