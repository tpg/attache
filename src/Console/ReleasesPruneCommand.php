<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TPG\Attache\ReleaseService;
use TPG\Attache\Server;

class ReleasesPruneCommand extends Command
{
    /**
     * @var ReleaseService
     */
    protected ReleaseService $releaseService;

    /**
     * Configure the command.
     */
    public function configure(): void
    {
        $this->setName('releases:prune')
            ->setDescription('Prune releases from the specified server. Retains the most recent two')
            ->addOption('count', 'o', InputOption::VALUE_REQUIRED, 'The number of releases to prune.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Do not confirm.')
            ->requiresConfig()
            ->requiresServer();
    }

    /**
     * Prune old releases from the server leaving at least the latest 2.
     *
     * @return int
     */
    public function fire(): int
    {
        $this->releaseService = (new ReleaseService($this->server))->fetch();

        $pruneIds = $this->getIdsToDelete($this->releaseService->list(), $this->option('count'));

        $this->validate($pruneIds);

        $this->releaseService->delete($pruneIds);

        $this->output->writeln('Pruned '.count($pruneIds).' releases from <info>'.$this->server->name().'</info>');

        return 0;
    }

    /**
     * This is a destructive command, so get user validation.
     *
     * @param array $pruneIds
     */
    protected function validate(array $pruneIds): void
    {
        if (in_array($this->releaseService->active(), $pruneIds, true)) {
            $this->output->writeln('<error>You cannot prune the currently active release.</error>');
            exit(1);
        }

        if (! $pruneIds) {
            $this->output->writeln('<error>There are no releases to prune.</error>');
            exit(1);
        }

        if (! $this->option('force') && ! $this->confirmDeletion($this->server, $pruneIds)) {
            $this->output->writeln('Cancelled.');
            exit(1);
        }
    }

    /**
     * Get an array of release IDs to remove from the server.
     *
     * @param array $releases
     * @param int|null $count
     * @return array
     */
    protected function getIdsToDelete(array $releases, ?int $count = null): array
    {
        $retain = $this->server->prune() ?: 2;

        if (! $count || $count > count($releases) - $retain) {
            $count = count($releases) - $retain;
        }

        return array_slice($releases, 0, $count);
    }

    /**
     * Confirm the deletion with the user.
     *
     * @param Server $server
     * @param array $ids
     * @return bool
     */
    protected function confirmDeletion(Server $server, array $ids): bool
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'you are about to delete <info>'.count($ids).
                '</info> releases from <info>'.$server->name().'</info>.'.PHP_EOL.
                '<error>This action cannot be undone.</error>'.PHP_EOL.'Are you sure? (y/N): ',
            false
        );

        return $helper->ask($this->input, $this->output, $question);
    }
}
