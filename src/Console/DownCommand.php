<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseService;

class DownCommand extends SymfonyCommand
{
    use Command;

    protected function configure()
    {
        $this->setName('releases:down')
            ->setDescription('Take the specific release offline')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server');

        $this->requiresConfig();
    }

    protected function fire(): int
    {
        $server = $this->config->server($this->argument('server'));

        (new ReleaseService($server))->down();

        $this->output->writeln('<info>'.$server->name().'</info> is offline');

        return 0;
    }
}
