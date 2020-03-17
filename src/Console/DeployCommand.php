<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Deployer;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Server;

/**
 * Class DeployCommand.
 */
class DeployCommand extends SymfonyCommand
{
    use Command;

    /**
     * @var Deployer
     */
    protected ?Deployer $deployer;

    public function __construct(string $name = null, ?ConfigurationProvider $configurationProvider = null, ?Deployer $deployer = null)
    {
        parent::__construct($name);
        $this->config = $configurationProvider;
        $this->deployer = $deployer;
    }

    /**
     * Deploy a new release to the specified server.
     */
    protected function configure(): void
    {
        $this->setName('deploy')
            ->setDescription('Run a deployment to the configured server')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server')
            ->addOption('prune', 'p', InputOption::VALUE_NONE, 'Prune old releases');

        $this->requiresConfig();
    }

    /**
     * @return int
     * @throws ConfigurationException
     */
    protected function fire(): int
    {
        $server = $this->config->server($this->argument('server'));

        $releaseId = date('YmdHis');

        $deployer = $this->getDeployer($server);
        $deployer->deploy($releaseId);

        $this->output->writeln('Release <info>'.$releaseId.'</info> is now live on <info>'.$server->name().'</info>');

        if ($this->option('prune')) {
            $command = $this->getApplication()->find('releases:prune');

            $command->run(new ArrayInput([
                'server' => 'production',
                '--force' => true,
            ]), $this->output);
        }

        return 0;
    }

    protected function getDeployer(Server $server): Deployer
    {
        if (! $this->deployer) {
            return new Deployer($this->config, $server, $this->input, $this->output);
        }

        return $this->deployer;
    }
}
