<?php

namespace TPG\Attache\Console;

class SshCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure(): void
    {
        $this->setName('ssh')
            ->setDescription('Open an SSH connection to the specified server')
            ->requiresServer()
            ->requiresConfig();
    }

    /**
     * Open an SSH connection to the specified server.
     *
     * @return int
     */
    protected function fire(): int
    {
        passthru('ssh '.$this->server->sshConnectionString());

        return 0;
    }
}
