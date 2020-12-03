<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use TPG\Attache\Contracts\InitializerContract;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Initializer;

class ServerAddCommand extends Command
{
    protected ?InitializerContract $initializer;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->setConfigurationProvider();
        $this->setInitializer();
    }

    public function setInitializer(?InitializerContract $initializer = null): self
    {
        $this->initializer = $initializer ?: new Initializer($this->filesystem);

        return $this;
    }

    protected function configure(): void
    {
        $this->setName('server:add')
            ->setDescription('Add a server')
            ->addArgument('name', InputArgument::REQUIRED, 'Server name')
            ->addOption('host', 's', InputOption::VALUE_REQUIRED, 'Server hostname')
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'SSH port')
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'SSH username')
            ->addOption('root', 'o', InputOption::VALUE_REQUIRED, 'Project root path')
            ->addOption('branch', 'b', InputOption::VALUE_REQUIRED, 'Git branch name', 'master')
            ->requiresConfig();
    }

    protected function fire(): int
    {
        $config = json_decode($this->filesystem->read($this->option('config')), true, 512, JSON_THROW_ON_ERROR);

        if (array_key_exists($this->argument('name'), $config['servers'])) {
            throw new ConfigurationException($this->argument('name').' already configured.');
        }

        $config['servers'][$this->argument('name')] = $this->getServerConfig();

        $this->initializer->save($this->option('config'), $config);

        return 0;
    }

    protected function getServerConfig(): array
    {
        return [
            'host' => $this->option('host') ?: $this->askFor('Hostname'),
            'port' => (int) $this->option('host') ?: (int) $this->askFor('SSH port number'),
            'user' => $this->option('host') ?: $this->askFor('SSH username'),
            'root' => $this->option('host') ?: $this->askFor('Remote project root path'),
            'branch' => $this->option('host') ?: $this->askFor('Git branch'),
        ];
    }

    protected function askFor(string $label)
    {
        $helper = $this->getHelper('question');
        $question = new Question('<info>'.$label.': </info>');

        return $helper->ask($this->input, $this->output, $question);
    }
}
