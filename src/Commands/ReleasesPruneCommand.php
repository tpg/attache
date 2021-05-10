<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\ReleaseManager;

class ReleasesPruneCommand extends Command
{
    protected string $name = 'releases:prune';
    protected string $description = 'Prune releases from the server';
    protected bool $requireConfig = true;
    protected bool $requireServer = true;

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('retain', 'l', InputOption::VALUE_REQUIRED, 'The number of releases to retain (including the active release)', 2);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->setReleaseManager(new ReleaseManager($this->server));
    }

    protected function fire(): int
    {
        $release = $this->releaseManager->fetch();

        $index = collect($release->available())->search($release->active());

        if ($index + 1 > $this->option('retain')) {
            $this->releaseManager->prune((int) $this->option('retain'), function ($release) {
                $this->output->writeln('Pruned <comment>'.$release.'</comment>');
            });
        }

        return 0;
    }
}
