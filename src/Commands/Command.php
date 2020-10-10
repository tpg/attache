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
use TPG\Attache\Server;

abstract class Command extends SymfonyCommand
{
    protected InputInterface $input;

    protected OutputInterface $output;

    protected ?ConfigurationProvider $configurationProvider = null;

    protected Filesystem $filesystem;

    protected ?Server $server;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->setFilesystem();
        $this->setConfigurationProvider();
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

        $this->loadConfig();

        return $this->fire();
    }

    public function setFilesystem(Filesystem $filesystem = null): self
    {
        $this->filesystem = $filesystem ?? new Filesystem(new Local(__DIR__));

        return $this;
    }

    public function setConfigurationProvider(ConfigurationProvider $configurationProvider = null): self
    {
        $this->configurationProvider = $configurationProvider ?? new ConfigurationProvider($this->filesystem);

        return $this;
    }

    protected function loadConfig(): void
    {
        if ($this->input->hasOption('config') && ! $this->configurationProvider->configured()) {
            $this->configurationProvider->load($this->option('config'));
        }
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

    protected function server(): Server
    {
        $name = $this->argument('server');

        if ($name && $this->input->hasArgument('server')) {
            return $this->configurationProvider->server($name);
        }

        return $this->configurationProvider->defaultServer();
    }
}
