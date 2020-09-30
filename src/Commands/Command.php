<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\ConfigurationProvider;

class Command extends SymfonyCommand
{
    /**
     * @var InputInterface
     */
    protected InputInterface $input;
    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;
    /**
     * @var ?ConfigurationProvider
     */
    protected ?ConfigurationProvider $configurationProvider;

    /**
     * Command constructor.
     * @param string|null $name
     * @param ?ConfigurationProvider $configurationProvider
     */
    public function __construct(string $name = null, ?ConfigurationProvider $configurationProvider = null)
    {
        parent::__construct($name);

        $this->setConfigurationProvider($configurationProvider);
    }

    protected function setConfigurationProvider(?ConfigurationProvider $configurationProvider): void
    {
        $this->configurationProvider = $configurationProvider;
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
