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
            ->addOption('unlock', 'u', InputOption::VALUE_NONE, 'Clear a lock before deployment')
            ->addOption('branch', 'b', InputOption::VALUE_REQUIRED, 'Override the configured branch')
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
        $this->checkIfLocked();

        $releaseId = date('YmdHis');


        $this->lock();
        $this->getDeployer($this->server)->deploy($releaseId);
        $this->unlock();

        $this->output->writeln('Release <info>'.$releaseId.'</info> is now live on <info>'.$this->server->name().'</info>');

        if ($this->server->prune() || $this->option('prune')) {
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

    protected function checkIfLocked(): void
    {
        $releaseService = new ReleaseService($this->server);

        if ($this->option('unlock')) {
            $releaseService->unlock();
        }

        if ($releaseService->locked()) {
            $this->output->writeln($this->error($this->server->name().' is locked. Another deployment may be in progress.'));
            $this->output->writeln($this->info('Use "--unlock" to forcibly remove the lock before deployment'));
            exit(101);
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

    protected function lock(): void
    {
        (new ReleaseService($this->server))->lock();
    }

    protected function unlock(): void
    {
        (new ReleaseService($this->server))->unlock();
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
