<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\ReleaseManager;

class ReleasesActivateCommand extends Command
{
    protected string $name = 'releases:activate';
    protected string $description = 'Activate a specified release ID';
    protected bool $requireConfig = true;
    protected bool $requireServer = true;

    protected function configure(): void
    {
        $this->addArgument(
            'release',
            InputArgument::REQUIRED,
            'The ID of the release to activate'
        );

        parent::configure();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->setReleaseManager(new ReleaseManager($this->server));
    }

    protected function fire(): int
    {
        $id = $this->argument('release');

        if ($id === 'latest') {
            $id = null;
        }

        $activated = $this->releaseManager->activate($id);

        $this->output->writeln('Release <info>'.$activated.'</info> activated on <comment>'.$this->server->name().'</comment>');

        return 0;
    }
}
