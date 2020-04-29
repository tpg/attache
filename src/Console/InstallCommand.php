<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Input\InputOption;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Deployer;
use TPG\Attache\ReleaseService;

class InstallCommand extends Command
{
    protected ?Deployer $deployer;

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
        $this->setName('install')
            ->setDescription('Initial installation and deployment to the specified server')
            ->addOption('env', 'e', InputOption::VALUE_REQUIRED, 'The installation .env file', '.env.example')
            ->requiresConfig()
            ->requiresServer();
    }

    /**
     * Run the server installation.
     *
     * @return int
     */
    protected function fire(): int
    {
        $service = new ReleaseService($this->server);

        if ($service->hasInstallation()) {
            $this->output->writeln(
                '<error>There is already an installation on '
                .$this->server->name()
                .'. Did you mean to deploy instead?</error>'
            );
            exit(1);
        }

        $releaseId = date('YmdHis');

        $env = $this->getEnv($this->option('env'));

        (new Deployer($this->config, $this->server, $this->input, $this->output))->install($releaseId, $env);

        $this->output->writeln('Installation is complete and <info>'.$releaseId.
            '</info> is now live on <info>'.$this->server->name().'</info>');

        return 0;
    }

    /**
     * Get the contents of the specified .env file or the default .env.example file.
     *
     * @param string|null $filename
     * @return false|string
     */
    protected function getEnv(?string $filename = null)
    {
        if ($filename && ! file_exists($filename)) {
            $this->output->writeln($this->error($filename.' does not exist.'));
            exit(1);
        }

        if (! $filename) {
            $filename = '.env.example';
        }

        try {
            return $this->updateEnv(file_get_contents($filename));
        } catch (\Exception $e) {
            $this->output->writeln($this->error($filename.' does not exist'));
        }
    }

    /**
     * @param string $env
     * @return string
     */
    protected function updateEnv(string $env): string
    {
        $env = explode(PHP_EOL, $env);

        array_walk($env, function (&$line) {
            $parts = explode('=', $line);
            switch ($parts[0]) {
                case 'APP_ENV':
                    $parts[1] = 'production';
                    break;
                case 'APP_DEBUG':
                    $parts[1] = 'true';
                    break;
            }
            $env = implode('=', $parts);
        });

        return implode(PHP_EOL, $env);
    }
}
