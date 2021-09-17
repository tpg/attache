<?php

namespace TPG\Attache\Console;

use TPG\Attache\ReleaseService;

class ReleasesRollbackCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure(): void
    {
        $this->setName('releases:rollback')
            ->setDescription('Rollback to the previous release on the specified server')
            ->requiresConfig()
            ->requiresServer();
    }

    /**
     * Rollback the active release by one.
     *
     * @return int
     */
    protected function fire(): int
    {
        $releaseService = (new ReleaseService($this->server))->fetch();

        $rollbackId = $this->getRollbackId($releaseService);

        if (! $rollbackId) {
            throw new \RuntimeException('Could not determine the currently active release');
        }

        $releaseService->activate($rollbackId);

        $this->output->writeln('Rolled back to <info>'.$rollbackId.'</info>');

        return 0;
    }

    /**
     * Get the release ID of the previous release.
     *
     * @param  ReleaseService  $releaseService
     * @return mixed|null
     */
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
