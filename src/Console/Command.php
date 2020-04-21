<?php

namespace TPG\Attache\Console;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Server;

abstract class Command extends SymfonyCommand
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
     * @var ConfigurationProvider|null
     */
    protected ?ConfigurationProvider $config = null;

    /**
     * @var Server|null
     */
    protected ?Server $server = null;

    /**
     * @param string|null $name
     * @param ConfigurationProvider|null $configurationProvider
     */
    public function __construct(string $name = null, ?ConfigurationProvider $configurationProvider = null)
    {
        parent::__construct($name);

        if ($configurationProvider) {
            $this->setConfigurationProvider($configurationProvider);
        }
    }

    /**
     * Set the instance of the ConfigurationProvider.
     *
     * @param ConfigurationProvider $configurationProvider
     * @return $this
     */
    public function setConfigurationProvider(ConfigurationProvider $configurationProvider): self
    {
        $this->config = $configurationProvider;

        return $this;
    }

    /**
     * Ensure that a configuration is provided for this command.
     *
     * @return $this
     */
    protected function requiresConfig(): self
    {
        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'Path to the Attaché config file',
            '.attache.json');

        return $this;
    }

    /**
     * Ensure that a server configuration is required for this command.
     *
     * @return $this
     */
    protected function requiresServer(): self
    {
        $this->addArgument('server', InputArgument::OPTIONAL, 'The name of the configured server');

        return $this;
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ConfigurationException
     * @throws FileNotFoundException
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $this->loadConfig();

        $this->setConfiguredServer();

        return (int) $this->fire();
    }

    /**
     * Run the command.
     *
     * @return int
     */
    protected function fire(): int
    {
        return 0;
    }

    /**
     * Load the provided config file.
     *
     * @throws FileNotFoundException
     * @throws \JsonException
     */
    protected function loadConfig(): void
    {
        if (! $this->config && $this->input->hasOption('config')) {
            $this->config = new ConfigurationProvider($this->option('config'));
        }
    }

    /**
     * Set the Server object from the argument.
     *
     * @throws ConfigurationException
     */
    protected function setConfiguredServer(): void
    {
        if (! $this->input->hasArgument('server')) {
            return;
        }

        $serverString = $this->config->servers()->first()->name();

        if ($this->config
            && $this->input->hasArgument('server')
            && $this->config->servers()->count() > 1) {
            $serverString = $this->argument('server') ?: $this->config->default();
        }

        if (! $serverString) {
            throw new ConfigurationException('No server specified');
        }
        $this->server = $this->config->server($serverString);
    }

    /**
     * Get an option value.
     *
     * @param $key
     * @return bool|string|string[]|null
     */
    public function option($key)
    {
        return $this->input->getOption($key);
    }

    /**
     * Get an argument value.
     *
     * @param $key
     * @return string|string[]|null
     */
    public function argument($key)
    {
        return $this->input->getArgument($key);
    }

    /**
     * Info styling.
     *
     * @param string $message
     * @return string
     */
    public function info(string $message)
    {
        return '<info>'.$message.'</info>';
    }

    /**
     * Error styling.
     *
     * @param string $message
     * @return string
     */
    public function error(string $message)
    {
        return '<error>'.$message.'</error>';
    }
}
