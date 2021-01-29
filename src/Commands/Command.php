<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    protected InputInterface $input;
    protected OutputInterface $output;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
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

        return $this->fire();
    }

    protected function option(string $key)
    {
        return $this->input->getOption($key);
    }

    protected function argument(string $key)
    {
        return $this->input->getArgument($key);
    }

    abstract protected function fire(): int;
}
