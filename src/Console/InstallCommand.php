<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Input\InputOption;
use TPG\Attache\Deployer;
use TPG\Attache\ReleaseService;

/**
 * Class InstallCommand.
 */
class InstallCommand extends Command
{
    protected function configure()
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
        $releases = (new ReleaseService($this->server))->fetch();

        if (count($releases->list()) > 0) {
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
            return file_get_contents($filename);
        } catch (\Exception $e) {
            $this->output->writeln($this->error($filename.' does not exist'));
        }
    }
}
