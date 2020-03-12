<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Server;
use TPG\Attache\Ssh;
use TPG\Attache\Task;

/**
 * Class DeployCommand.
 */
class DeployCommand extends SymfonyCommand
{
    use Command;

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

        $tasks = $this->getTasks($server, $releaseId);

        foreach ($tasks as $task) {
            if ($task->server()) {
                (new Ssh($task))->run(function ($task, $type, $output) {
                    $this->output->writeln($output);
                });
            } else {
                $process = Process::fromShellCommandline($task->script());
                $process->run(function ($type, $output) {
                    $this->output->writeln($output);
                });
            }
        }

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

    protected function getTasks(Server $server, string $releaseId): array
    {
        return [
            new Task('yarn prod'),
            $this->getInstallTask($server, $releaseId),
            $this->getAssetsTask($server, $releaseId),
            $this->getLiveTask($server, $releaseId),
        ];
    }

    protected function getInstallTask(Server $server, string $releaseId): Task
    {
        $releasePath = $server->path('releases').'/'.$releaseId;

        $commands = [
            'git clone -b '.$server->branch().' --depth=1 '.$this->config->repository().' '.$releasePath,
            'cd '.$releasePath.' && composer install --no-dev',
            'rm -rf '.$releasePath.'/storage',
            'ln -nfs '.$server->path('storage').' '.$releasePath.'/storage',
            'ln -nfs '.$server->path('env').' '.$releasePath.'/.env',
            'php artisan migrate --force',
            'php artisan storage:link',
        ];

        return new Task(implode(' && ', $commands), $server);
    }

    protected function getAssetsTask(Server $server, string $releaseId): Task
    {
        $releasePath = $server->path('releases').'/'.$releaseId;

        $commands = [
            'scp -P '.$server->port().' -r public/js '.$server->user().'@'.$server->host().':'.$releasePath.'/public',
            'scp -P '.$server->port().' -r public/css '.$server->user().'@'.$server->host().':'.$releasePath.'/public',
            'scp -P '.$server->port().' -r public/mix-manifest.json '.$server->user().'@'.$server->host().':'.$releasePath.'/public',
        ];

        return new Task(implode(' && ', $commands));
    }

    protected function getLiveTask(Server $server, string $releaseId): Task
    {
        $releasePath = $server->path('releases').'/'.$releaseId;

        $command = 'ln -nfs '.$releasePath.' '.$server->path('serve');

        return new Task($command, $server);
    }
}
