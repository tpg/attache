<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\ConfigurationProvider;

abstract class Command extends SymfonyCommand
{
    protected InputInterface $input;

    protected OutputInterface $output;

    protected ?ConfigurationProvider $configurationProvider = null;

    protected Filesystem $filesystem;

    public function setConfigurationProvider(ConfigurationProvider $configurationProvider): void
    {
        $this->configurationProvider = $configurationProvider;
    }

    protected function requiresConfig(): self
    {
        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'Path to the config file',
            '.attache.json'
        );

        return $this;
    }

    protected function requiresServer(): self
    {
        $this->addArgument(
            'server',
            InputArgument::OPTIONAL,
            'The name of the configured server'
        );

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;

        $this->output = $output;

        $this->setFilesystem();
        $this->initConfiguration();

        return $this->fire();
    }

    public function setFilesystem(Filesystem $filesystem = null): void
    {
        $this->filesystem = $filesystem ?? new Filesystem(new Local(__DIR__));
    }

    protected function initConfiguration(): void
    {
        if (! $this->configurationProvider && $this->input->hasOption('config')) {
            $this->configurationProvider = new ConfigurationProvider(
                $this->filesystem
            );
        }

        if ($this->configurationProvider && $this->input->hasOption('config')) {
            $this->loadConfig();
        }
    }

    protected function loadConfig(): void
    {
        $this->configurationProvider->load($this->option('config'));
    }

    protected function fire(): int
    {
        return 0;
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
