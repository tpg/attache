<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseService;
use TPG\Attache\Ssh;
use TPG\Attache\Task;

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

        $rollbackId = $this->getRollbackId($releaseService);

        if (!$rollbackId) {
            throw new \RuntimeException('Could not determine the currently active release');
        }

        $releaseService->activate($rollbackId);

        $this->output->writeln('Rolled back to <info>'.$rollbackId.'</info>');

        return 0;
    }

    protected function getRollbackId(ReleaseService $releaseService)
    {
        $activeIndex = array_search($releaseService->active(), $releaseService->list(), true);

        $rollbackId = null;
        if ($activeIndex > 0) {
            $rollbackId = $releaseService->list()[$activeIndex - 1];
        }

        return $rollbackId;
    }
}
