<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseService;

/**
 * Class ReleasesActivateCommand.
 */
class ReleasesActivateCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName('releases:activate')
            ->setDescription('Activate a release on the specified server')
            ->addArgument('release', InputArgument::REQUIRED, 'The ID of the release to activate')
            ->requiresServer()
            ->requiresConfig();
    }

    /**
     * Activate a release.
     *
     * @return int
     */
    protected function fire(): int
    {
        $id = $this->argument('release');

        $releaseService = (new ReleaseService($this->server))->fetch();

        if ($id !== 'latest' && ! $releaseService->exists($id)) {
            throw new \RuntimeException('A release with ID '.$id.' does not exist');
        }

        $this->output->writeln('Setting active release to <info>'.$id.'</info>...');
        $releaseService->activate($id);

        return 0;
    }
}
