<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\ConfigurationProvider;

class Command extends SymfonyCommand
{
    protected InputInterface $input;

    protected OutputInterface $output;

    protected ?ConfigurationProvider $configurationProvider;

    protected Filesystem $filesystem;

    public function __construct(string $name = null, ?ConfigurationProvider $configurationProvider = null, ?Filesystem $filesystem = null)
    {
        parent::__construct($name);

        $this->setFilesystem($filesystem);
        $this->setConfigurationProvider($configurationProvider);
    }

    protected function setConfigurationProvider(?ConfigurationProvider $configurationProvider): void
    {
        $this->configurationProvider = $configurationProvider ?? new ConfigurationProvider($this->filesystem);
    }

    protected function setFilesystem(?Filesystem $filesystem): void
    {
        $this->filesystem = $filesystem ?? new Filesystem(new Local(getcwd()));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;

        $this->output = $output;

        return $this->fire();
    }

    protected function fire(): int
    {
        return 0;
    }

    protected function input(): InputInterface
    {
        return $this->input;
    }

    protected function output(): OutputInterface
    {
        return $this->output;
    }

    protected function option($key)
    {
        return $this->input->getOption($key);
    }

    protected function argument($key)
    {
        return $this->input->getArgument($key);
    }
}
