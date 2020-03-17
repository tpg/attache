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

    /**
     * @var string|null
     */
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
        $tasks = $this->getTasks($releaseId, $install);

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
                (new Ssh($task))->tty()->run(function ($task, $type, $output) {
                    $this->output->writeln($output);
                });
            } else {
                $process = Process::fromShellCommandline($task->script());
                $process
                    ->setTty(Process::isTtySupported())
                    ->run(function ($type, $output) {
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
    public function getTasks(string $releaseId, bool $install = false): array
    {
        return [
            $this->buildTask(),
            $this->DeploymentTask(
                $releaseId,
                $this->server->migrate(),
                $install
                ),
            $this->assetTask($releaseId),
            $this->liveTask($releaseId),
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

        $commands = [
            ...$this->server->script('before-build'),
            $command,
            ...$this->server->script('after-build'),
        ];

        return new Task(implode(PHP_EOL, $commands));
    }

    /**
     * The deployment task.
     *
     * @param string $releaseId
     * @param bool $migrate
     * @param bool $install
     * @return Task
     */
    protected function DeploymentTask(string $releaseId, $migrate = false, $install = false): Task
    {
        $releasePath = $this->releasePath($releaseId);

        $commands = array_filter([
            ...$this->server->script('before-deploy'),
            'printf "\033c"',
            ...$this->cloneSteps($releasePath),
            ...$this->getComposer($releasePath),
            ...$this->composerSteps($releasePath),
            ...$this->installationSteps($install, $releasePath),
            ...$this->envSteps($releasePath),
            ...$this->symlinkSteps($releasePath),
            ...$this->migrationSteps($migrate, $releasePath),
            ...$this->server->script('after-deploy'),
        ], static function ($command) {
            return $command !== null;
        });

        return new Task(implode(PHP_EOL, $commands), $this->server);
    }

    /**
     * Git clone steps.
     *
     * @param string $releasePath
     * @return array
     */
    protected function cloneSteps(string $releasePath): array
    {
        return [
            ...$this->server->script('before-clone'),
            'git clone -b '.$this->server->branch().' --depth=1 '.$this->config->repository().' '.$releasePath,
            ...$this->server->script('after-clone'),
        ];
    }

    /**
     * Get a copy of composer.
     *
     * @param string $releasePath
     * @return array
     */
    protected function getComposer(string $releasePath): array
    {
        if ($this->server->composer('local')) {
            return [
                ...$this->server->script('before-prep-composer'),
                'if test ! -f "'.$this->server->composerBin().'"; then',
                'curl -sS https://getcomposer.org/installer -o composer-installer.php',
                $this->server->phpBin().' composer-installer.php --install-dir='.$this->server->root().' --filename='.$this->server->composer('bin'),
                'rm composer-installer.php',
                'else',
                $this->server->composerBin().' self-update',
                'fi',
                ...$this->server->script('after-prep-composer'),
            ];
        }

        return [];
    }

    /**
     * The composer install steps.
     *
     * @param string $releasePath
     * @return array
     */
    protected function composerSteps(string $releasePath): array
    {
        $composerExec = $this->server->phpBin().' '.$this->server->composerBin();

        return [
            ...$this->server->script('before-composer'),
            'cd '.$releasePath.PHP_EOL
            .$composerExec.' install --no-dev --ansi',
            ...$this->server->script('after-composer'),
        ];
    }

    /**
     * Get the installation steps if needed.
     *
     * @param bool $install
     * @param string $releasePath
     * @return array
     */
    protected function installationSteps(bool $install, string $releasePath): array
    {
        return [
            ...$this->server->script('before-install'),
            $install
                ? 'mv '.$releasePath.'/storage '.$this->server->path('storage')
                : 'rm -rf '.$releasePath.'/storage',
            ...$this->server->script('after-install'),
        ];
    }

    /**
     * Steps to place a new `.env` file.
     *
     * @param string $releasePath
     * @return array
     */
    protected function envSteps(string $releasePath): array
    {
        $env = null;

        if ($this->installEnv) {
            $env = 'cat > '.$this->server->path('env').' << \'ENV-EOF\''.PHP_EOL
                .$this->installEnv.PHP_EOL
                .'ENV-EOF';
        }

        return [$env];
    }

    /**
     * The symbolic link steps.
     *
     * @param string $releasePath
     * @return array
     */
    protected function symlinkSteps(string $releasePath): array
    {
        return [
            ...$this->server->script('before-symlinks'),
            'ln -nfs '.$this->server->path('storage').' '.$releasePath.'/storage',
            'ln -nfs '.$this->server->path('env').' '.$releasePath.'/.env',
            $this->server->phpBin().' artisan storage:link',
            ...$this->server->script('after-symlinks'),
        ];
    }

    /**
     * The database migration steps if needed.
     *
     * @param bool $migrate
     * @param string $releasePath
     * @return array
     */
    protected function migrationSteps(bool $migrate, string $releasePath): array
    {
        return $migrate ? [
            ...$this->server->script('before-migrate'),
            'php artisan migrate --force',
            ...$this->server->script('after-migrate'),
        ] : [];
    }

    /**
     * The release path to deploy into.
     *
     * @param $releaseId
     * @return string
     */
    protected function releasePath($releaseId): string
    {
        return $this->server->path('releases').'/'.$releaseId;
    }

    /**
     * The asset migration task.
     *
     * @param string $releaseId
     * @return Task
     */
    protected function assetTask(string $releaseId): Task
    {
        $releasePath = $this->server->path('releases').'/'.$releaseId;

        $commands = [
            ...$this->server->script('before-assets'),
            'printf "\033c"',
            'echo "Copying assets..."',
            'scp -P '.$this->server->port().' -r public/js '.$this->server->user().'@'.$this->server->host().':'.$releasePath.'/public',
            'scp -P '.$this->server->port().' -r public/css '.$this->server->user().'@'.$this->server->host().':'.$releasePath.'/public',
            'scp -P '.$this->server->port().' -r public/mix-manifest.json '.$this->server->user().'@'.$this->server->host().':'.$releasePath.'/public',
            ...$this->server->script('after-assets'),
        ];

        return new Task(implode(PHP_EOL, $commands));
    }

    /**
     * The live task.
     *
     * @param string $releaseId
     * @return Task
     */
    protected function liveTask(string $releaseId): Task
    {
        $releasePath = $this->server->path('releases').'/'.$releaseId;

        $commands = [
            ...$this->server->script('before-live'),
            'ln -nfs '.$releasePath.' '.$this->server->path('serve'),
            'printf "\033c"',
            ...$this->server->script('after-live'),
        ];

        return new Task(implode(PHP_EOL, $commands), $this->server);
    }
}
