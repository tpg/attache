<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseService;
use TPG\Attache\Ssh;

/**
 * Class ReleasesRollbackCommand.
 */
class ReleasesRollbackCommand extends SymfonyCommand
{
    use Command;

    /**
     * Rollback the current release to the previous one.
     */
    protected function configure(): void
    {
        $this->setName('releases:rollback')
            ->setDescription('Rollback to the previous release on the specified server')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server');

        $this->requiresConfig();
    }

    /**
     * @return int
     */
    protected function fire(): int
    {
        $server = $this->config->server($this->argument('server'));

        $releaseService = (new ReleaseService($server))->fetch();

        $activeIndex = array_search($releaseService->active(), $releaseService->list(), true);

        if ($activeIndex === false) {
            throw new \RuntimeException('Could not determine the currently active release');
        }

        if ($activeIndex > 0) {
            $rollbackId = $releaseService->list()[$activeIndex - 1];

            $command = 'ln -nfs '.$server['root'].'/releases/'.$rollbackId.' '.
                $server['root'].'/live';

            (new Ssh($server))->run($command, function ($outputs) use ($rollbackId) {
                $this->output->writeln('Rolled back to <info>'.$rollbackId.'</info>');
            });
        }

        return 0;
    }
}
