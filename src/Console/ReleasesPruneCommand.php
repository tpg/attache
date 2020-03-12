<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TPG\Attache\ReleaseService;
use TPG\Attache\Server;

/**
 * Class ReleasesPruneCommand.
 */
class ReleasesPruneCommand extends SymfonyCommand
{
    use Command;

    /**
     * Prune the releases on the server leaving the active one and the previous one.
     */
    public function configure()
    {
        $this->setName('releases:prune')
            ->setDescription('Prune releases from the specified server. Retains the most recent two')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server')
            ->addOption('count','o', InputOption::VALUE_REQUIRED, 'The number of releases to prune.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Do not confirm.');

        $this->requiresConfig();
    }

    /**
     * @return int
     */
    public function fire(): int
    {
        $server = $this->config->server($this->argument('server'));

        $releaseService = (new ReleaseService($server))->fetch();

        $pruneIds = $this->getIdsToDelete($releaseService->list(), $this->option('count'));

        if (in_array($releaseService->active(), $pruneIds, true)) {
            $this->output->writeln('<error>You cannot prune the currently active release.</error>');
            exit(1);
        }

        if (!$pruneIds) {
            $this->output->writeln('<error>There are no releases to prune.</error>');
            exit(1);
        }

        if (!$this->option('force') && !$this->confirmDeletion($server, $pruneIds)) {
            $this->output->writeln('Cancelled.');
            exit(1);
        }

        $releaseService->delete($pruneIds);

        $this->output->writeln('Pruned '.count($pruneIds).' releases from <info>'.$server->name().'</info>');

        return 0;
    }

    protected function getIdsToDelete(array $releases, ?int $count = null): array
    {
        if (!$count || $count > count($releases) - 2) {
            $count = count($releases) - 2;
        }

        return array_slice($releases, 0, $count);
    }

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
