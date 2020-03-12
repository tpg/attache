<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseService;

/**
 * Class ReleasesActivateCommand.
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
        $server = $this->config->server($this->argument('server'));
        $id = $this->argument('release');

        $releaseService = (new ReleaseService($server))->fetch();

        if ($id !== 'latest' && ! $releaseService->exists($id)) {
            throw new \RuntimeException('A release with ID '.$id.' does not exist');
        }

        $this->output->writeln('Setting active release to <info>'.$id.'</info>...');

        $releaseService->activate($id);

        return 0;
    }
}
