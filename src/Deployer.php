<?php

namespace TPG\Attache;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class Deployer.
 */
class Deployer
{
    /**
     * @var ConfigurationProvider
     */
    protected ConfigurationProvider $config;
    /**
     * @var Server
     */
    protected Server $server;
    /**
     * @var InputInterface
     */
    protected InputInterface $input;
    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

    protected ?string $installEnv = null;

    /**
     * @param ConfigurationProvider $config
     * @param Server $server
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(
        ConfigurationProvider $config,
        Server $server,
        InputInterface $input,
        OutputInterface $output)
    {
        $this->config = $config;
        $this->server = $server;
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Deploy a release.
     *
     * @param string $releaseId
     * @param bool $install
     */
    public function deploy(string $releaseId, bool $install = false): void
    {
        $tasks = $this->getTasks($this->server, $releaseId, $install);

        $this->executeTasks($tasks);
    }

    /**
     * Install a release.
     *
     * @param string $releaseId
     * @param string|null $env
     */
    public function install(string $releaseId, string $env = null): void
    {
        $this->installEnv = $env;
        $this->deploy($releaseId, true);
    }

    /**
     * Execute an array of tasks.
     *
     * @param array $tasks
     */
    protected function executeTasks(array $tasks): void
    {
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
    }

    /**
     * Get the tasks to execute.
     *
     * @param Server $server
     * @param string $releaseId
     * @param bool $install
     * @return array
     */
    protected function getTasks(Server $server, string $releaseId, bool $install = false): array
    {
        return [
            $this->buildTask(),
            $this->DeploymentTask(
                $server,
                $releaseId,
                $this->server->migrate(),
                $install
                ),
            $this->assetTask($server, $releaseId),
            $this->liveTask($server, $releaseId),
        ];
    }

    /**
     * The build task.
     *
     * @return Task
     */
    protected function buildTask(): Task
    {
        $command = 'yarn prod';

        return new Task($command);
    }

    /**
     * The deployment task.
     *
     * @param Server $server
     * @param string $releaseId
     * @param bool $migrate
     * @param bool $install
     * @return Task
     */
    protected function DeploymentTask(Server $server, string $releaseId, $migrate = false, $install = false): Task
    {
        $releasePath = $this->releasePath($server, $releaseId);

        $commands = array_filter([
            ...$this->cloneSteps($server, $releasePath),
            ...$this->getComposer($server, $releasePath),
            ...$this->composerSteps($server, $releasePath),
            ...$this->installationSteps($install, $server, $releasePath),
            ...$this->envSteps($server, $releasePath),
            ...$this->symlinkSteps($server, $releasePath),
            ...$this->migrationSteps($migrate, $server, $releasePath),
        ], static function ($command) {
            return $command !== null;
        });

        return new Task(implode(PHP_EOL, $commands), $server);
    }

    /**
     * Git clone steps.
     *
     * @param Server $server
     * @param string $releasePath
     * @return array
     */
    protected function cloneSteps(Server $server, string $releasePath): array
    {
        return [
            'git clone -b '.$server->branch().' --depth=1 '.$this->config->repository().' '.$releasePath,
        ];
    }

    /**
     * Get a copy of composer.
     *
     * @param Server $server
     * @param string $releasePath
     * @return array
     */
    protected function getComposer(Server $server, string $releasePath): array
    {
        if ($server->composer('local')) {
            return [
                'if test ! -f "'.$server->composerBin().'"; then',
                'curl -sS https://getcomposer.org/installer | '.$server->phpBin(),
                'mv composer.phar '.$server->composerBin(),
                'else',
                $server->composerBin().' self-update',
                'fi',
            ];
        }

        return [];
    }

    /**
     * The composer install steps.
     *
     * @param Server $server
     * @param string $releasePath
     * @return array
     */
    protected function composerSteps(Server $server, string $releasePath): array
    {
        $composerExec = $server->composerBin();

        return [
            'cd '.$releasePath.PHP_EOL
            .$composerExec.' install --no-dev',
        ];
    }

    /**
     * Get the installation steps if needed.
     *
     * @param bool $install
     * @param Server $server
     * @param string $releasePath
     * @return array
     */
    protected function installationSteps(bool $install, Server $server, string $releasePath): array
    {
        return $install
            ? [
                'mv '.$releasePath.'/storage '.$server->path('storage'),
            ]
            : [
                'rm -rf '.$releasePath.'/storage',
            ];
    }

    protected function envSteps(Server $server, string $releasePath): array
    {
        $env = 'cp '.$releasePath.'/.env.example '.$server->path('env');

        if ($this->installEnv) {
            $env = 'cat > '.$server->path('env').' << \'ENV-EOF\''.PHP_EOL
                .$this->installEnv.PHP_EOL
                .'ENV-EOF';
        }

        return [$env];
    }

    /**
     * The symbolic link steps.
     *
     * @param Server $server
     * @param string $releasePath
     * @return array
     */
    protected function symlinkSteps(Server $server, string $releasePath): array
    {
        return [
            'ln -nfs '.$server->path('storage').' '.$releasePath.'/storage',
            'ln -nfs '.$server->path('env').' '.$releasePath.'/.env',
            $server->phpBin().' artisan storage:link',
        ];
    }

    /**
     * The database migration steps if needed.
     *
     * @param bool $migrate
     * @param Server $server
     * @param string $releasePath
     * @return array
     */
    protected function migrationSteps(bool $migrate, Server $server, string $releasePath): array
    {
        return [
            $migrate ? 'php artisan migrate --force' : null,
        ];
    }

    /**
     * The release path to deploy into.
     *
     * @param Server $server
     * @param $releaseId
     * @return string
     */
    protected function releasePath(Server $server, $releaseId): string
    {
        return $server->path('releases').'/'.$releaseId;
    }

    /**
     * The asset migration task.
     *
     * @param Server $server
     * @param string $releaseId
     * @return Task
     */
    protected function assetTask(Server $server, string $releaseId): Task
    {
        $releasePath = $server->path('releases').'/'.$releaseId;

        $commands = [
            'scp -P '.$server->port().' -r public/js '.$server->user().'@'.$server->host().':'.$releasePath.'/public',
            'scp -P '.$server->port().' -r public/css '.$server->user().'@'.$server->host().':'.$releasePath.'/public',
            'scp -P '.$server->port().' -r public/mix-manifest.json '.$server->user().'@'.$server->host().':'.$releasePath.'/public',
        ];

        return new Task(implode(PHP_EOL, $commands));
    }

    /**
     * The live task.
     *
     * @param Server $server
     * @param string $releaseId
     * @return Task
     */
    protected function liveTask(Server $server, string $releaseId): Task
    {
        $releasePath = $server->path('releases').'/'.$releaseId;

        $command = 'ln -nfs '.$releasePath.' '.$server->path('serve');

        return new Task($command, $server);
    }
}
