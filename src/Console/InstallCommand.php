<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TPG\Attache\Deployer;
use TPG\Attache\ReleaseService;

class InstallCommand extends SymfonyCommand
{
    use Command;

    protected function configure()
    {
        $this->setName('install')
            ->setDescription('Initial installation and deployment to the specified server')
            ->addArgument('server', InputArgument::REQUIRED, 'The name of the configured server')
            ->addOption('env', 'e', InputOption::VALUE_REQUIRED, 'The installation .env file', '.env.example');

        $this->requiresConfig();
    }

    protected function fire(): int
    {
        $server = $this->config->server($this->argument('server'));

        $releases = (new ReleaseService($server))->fetch();

        if (count($releases->list()) > 0) {
            $this->output->writeln(
                '<error>There is already an installation on '
                .$server->name()
                .'. Did you mean to deploy instead?</error>'
            );
            exit(1);
        }

        $releaseId = date('YmdHis');

        $envFile = $this->option('env');

        if ($envFile && ! file_exists($envFile)) {
            $this->output->writeln('<error>'.$envFile.' does not exist.');
            exit(1);
        }

        $env = null;
        if ($envFile) {
            $env = $this->getEnv($envFile);
        }

        (new Deployer($this->config, $server, $this->input, $this->output))->install($releaseId, $env);

        $this->output->writeln('Installation is complete and <info>'.$releaseId.
            '</info> is now live on <info>'.$server->name().'</info>');

        return 0;

    }

    protected function getEnv(string $filename)
    {
        return file_get_contents($filename);
    }
}
