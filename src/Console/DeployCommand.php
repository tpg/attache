<?php

namespace TPG\Attache\Console;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Deployer;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Server;

/**
 * Class DeployCommand.
 */
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
        parent::__construct($name);

        $this->config = $configurationProvider;
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
            ->requiresConfig()
            ->requiresServer();
    }

    /**
     * Run the deployment.
     *
     * @return int
     * @throws ConfigurationException
     * @throws FileNotFoundException
     * @throws \JsonException
     */
    protected function fire(): int
    {
        $releaseId = date('YmdHis');

        $this->getDeployer($this->server)->deploy($releaseId);

        $this->output->writeln('Release <info>'.$releaseId.'</info> is now live on <info>'.$this->server->name().'</info>');

        if ($this->option('prune')) {
            $command = new ReleasesPruneCommand(null, $this->config);
            $command->execute(new ArrayInput([
                'server' => 'production',
                '--force' => true,
            ]), $this->output);
        }

        return 0;
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
            return new Deployer($this->config, $server, $this->input, $this->output);
        }

        return $this->deployer;
    }
}
