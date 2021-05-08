<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Contracts\ConfigurationProviderInterface;
use TPG\Attache\Contracts\InitializerInterface;
use TPG\Attache\Initializer;

abstract class Command extends SymfonyCommand
{
    private Filesystem $filesystem;

    protected InputInterface $input;
    protected OutputInterface $output;

    protected string $name;
    protected string $description;
    protected bool $requireConfig = false;
    protected bool $requireServer = false;

    protected ?InitializerInterface $initializer = null;
    protected ?ConfigurationProviderInterface $configurationProvider = null;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->setFilesystem($filesystem);
    }

    public function setFilesystem(Filesystem $filesystem): void
    {
        $this->filesystem = $filesystem;
    }

    protected function configure(): void
    {
        $this->setName($this->name)
            ->setDescription($this->description);

        if ($this->requireConfig) {
            $this->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'The name of the configuration file',
                '.attache.json',
            );
        }

        if ($this->requireServer) {
            $this->addArgument(
                'server',
                InputArgument::OPTIONAL,
                'The name of the configured server'
            );
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->setInitializer(new Initializer($this->filesystem));

        if ($this->requireConfig) {
            $this->setConfigurationProvider(
                new ConfigurationProvider($this->filesystem, $input->getOption('config'))
            );
        }
    }

    public function setInitializer(InitializerInterface $initializer): void
    {
        $this->initializer = $initializer;
    }

    public function setConfigurationProvider(ConfigurationProviderInterface $configurationProvider): void
    {
        $this->configurationProvider = $configurationProvider;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        set_time_limit(0);

        $this->input = $input;
        $this->output = $output;

        return (int) $this->fire();
    }

    protected function option(string $key): string | array | bool | null
    {
        return $this->input->getOption($key);
    }

    public function argument(string $key): array | string | null
    {
        return $this->input->getArgument($key);
    }

    abstract protected function fire(): int;
}
