<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseService;

class ReleasesDeleteCommand extends Command
{
    /**
     * @var ReleaseService
     */
    protected ReleaseService $releaseService;

    protected function configure(): void
    {
        $this->setName('releases:delete')
            ->setDescription('Remove a release from the specified server.')
            ->addOption('force', 'Do not confirm')
            ->addArgument('id', InputArgument::REQUIRED, 'The release to delete.')
            ->requiresConfig()
            ->requiresServer();
    }

    protected function fire(): int
    {
        $this->releaseService = (new ReleaseService($this->server))->fetch();

        $id = $this->argument('id');

        $this->validate($id);

        $this->releaseService->delete([$id]);

        $this->output->writeln('Deleted release '.$id.' from <info>'.$this->server->name().'</info>');

        return 0;
    }

    protected function validate(string $id): void
    {
        if (! in_array($id, $this->releaseService->list(), true)) {
            $this->output->writeln('<error>The release '.$id.' does not exist on '.$this->server->name().'.</error>');
            exit(1);
        }

        if ($id === $this->releaseService->active()) {
            $this->output->writeln('<error>You cannot delete the current active release.</error>');
            exit(1);
        }
    }
}
