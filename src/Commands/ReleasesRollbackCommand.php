<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\ReleaseManager;

class ReleasesRollbackCommand extends Command
{
    protected string $name = 'releases:rollback';
    protected string $description = 'Rollback to the previous release';
    protected bool $requireConfig = true;
    protected bool $requireServer = true;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->setReleaseManager(new ReleaseManager($this->server));
    }

    protected function fire(): int
    {
        $release = $this->releaseManager->fetch();

        $index = collect($release->available())->search($release->active());

        if ($index === 0) {
            throw new ConfigurationException('There are no releases to rollback to');
        }

        $activated = $this->releaseManager->activate($release->available()[$index - 1]);

        $this->output->writeln('Release <info>'.$activated.'</info> activated on <comment>'.$this->server->name().'</comment>');

        return 0;
    }
}
