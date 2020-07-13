<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Deployer;
use TPG\Attache\ReleaseService;
use TPG\Attache\Server;

class DeployCommand extends Command
{
    /**
     * @var Deployer
     */
    protected ?Deployer $deployer;

    /**
     * DeployCommand constructor.
     * @param string|null $name
     * @param ConfigurationProvider|null $configurationProvider
     * @param Deployer|null $deployer
     */
    public function __construct(string $name = null, ?ConfigurationProvider $configurationProvider = null, ?Deployer $deployer = null)
    {
        parent::__construct($name, $configurationProvider);

        $this->deployer = $deployer;
    }

    /**
     * Configure the command.
     */
    protected function configure(): void
    {
        $this->setName('deploy')
            ->setDescription('Run a deployment to the configured server')
            ->addOption('prune', 'p', InputOption::VALUE_NONE, 'Prune old releases')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Deploy even if there is no installation present at the target')
            ->requiresConfig()
            ->requiresServer();
    }

    /**
     * Run the deployment.
     *
     * @return int
     * @throws \Exception
     */
    protected function fire(): int
    {
        $this->checkForInstallation();

        $releaseId = date('YmdHis');

        $this->getDeployer($this->server)->deploy($releaseId);

        $this->output->writeln('Release <info>'.$releaseId.'</info> is now live on <info>'.$this->server->name().'</info>');

        if ($this->option('prune')) {
            $command = new ReleasesPruneCommand(null, $this->config);

            $command->run(new ArrayInput($this->getPruneArguments()), $this->output);
        }

        return 0;
    }

    protected function checkForInstallation(): void
    {
        if (! $this->option('force') && ! (new ReleaseService($this->server))->hasInstallation()) {
            $this->output->writeln($this->error('No installation at target: '.$this->server->root().'.'));
            $this->output->writeln($this->info('Try running "attache install '.$this->server->name().'" first.'));
            exit(100);
        }
    }

    /**
     * Get the Deployer instance.
     *
     * @param Server $server
     * @return Deployer
     */
    protected function getDeployer(Server $server): Deployer
    {
        if (! $this->deployer) {
            $deployer = new Deployer($this->config, $server, $this->input, $this->output);
            $deployer->tty();
            $this->deployer = $deployer;
        }

        return $this->deployer;
    }

    /**
     * Get arguments and options to pass to `PruneCommand`.
     *
     * @return array
     */
    protected function getPruneArguments(): array
    {
        return [
            'server' => $this->argument('server'),
            '--force' => true,
        ];
    }
}
