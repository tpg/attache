<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\ConfigurationProvider;

trait Command
{
    protected InputInterface $input;

    protected OutputInterface $output;

    protected ConfigurationProvider $config;

    protected function requiresConfig(): void
    {
        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'Path to the Attaché config file',
            '.attache.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if ($this->input->hasOption('config')) {
            $this->loadConfig();
        }

        return (int) $this->fire();
    }

    protected function fire(): int
    {
        //
    }

    protected function loadConfig(): void
    {
        $this->config = (new ConfigurationProvider($this->option('config')));
    }

    public function option($key)
    {
        return $this->input->getOption($key);
    }

    public function argument($key)
    {
        return $this->input->getArgument($key);
    }
}
