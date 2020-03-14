<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseService;

class UpCommand extends SymfonyCommand
{
    use Command;

    protected function configure()
    {
        $this->setName('releases:up')
            ->setDescription('Put the specific release online')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server');

        $this->requiresConfig();
    }

    protected function fire(): int
    {
        $server = $this->config->server($this->argument('server'));

        (new ReleaseService($server))->up();

        $this->output->writeln('<info>'.$server->name().'</info> is online');

        return 0;
    }
}
