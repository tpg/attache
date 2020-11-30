<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Input\InputArgument;
use TPG\Attache\ReleaseManager;

class ReleaseActivateCommand extends Command
{
    /**
     * @var ReleaseManager
     */
    protected ReleaseManager $releaseManager;

    protected function configure(): void
    {
        $this->setName('release:activate')
            ->setDescription('Activate a release by id')
            ->addArgument('release', InputArgument::REQUIRED, 'The release to activate')
            ->requiresConfig()
            ->requiresServer();
    }

    public function setReleaseManager(ReleaseManager $releaseManager): void
    {
        $this->releaseManager = $releaseManager;
    }

    protected function fire(): int
    {
        $releaseManager = $this->releaseManager ?? new ReleaseManager($this->server());
        $release = $releaseManager->activate($this->argument('release'));

        $this->print($release);

        return 0;
    }

    protected function print(string $release): void
    {
        $this->output->writeln('Activated release '.$release.' on '.$this->server()->name());
    }
}
