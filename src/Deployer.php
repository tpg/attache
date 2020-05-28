<?php

namespace TPG\Attache;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        set_time_limit(0);

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
        set_time_limit(0);

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
                $code = (new Ssh($task))->tty()->run(function ($task, $type, $output) {
                    $this->output->writeln($output);
                });

                if ($code !== 0) {
                    throw new \RuntimeException('One or more tasks did not complete correctly');
                }
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
     * @param string $releaseId
     * @param bool $install
     * @return array
     */
    public function getTasks(string $releaseId, bool $install = false): array
    {
        return [
            $this->buildTask(),
            $this->deploymentTask(
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
    protected function deploymentTask(string $releaseId, $migrate = false, $install = false): Task
    {
        $releasePath = $this->releasePath($releaseId);

        $commands = array_filter([
            'printf "\033c"',
            'cd '.$this->server->root(),
            ...$this->server->script('before-deploy'),
            ...$this->cloneSteps($releasePath),
            ...$this->getComposer($releasePath),
            ...$this->envSteps($releasePath),
            ...$this->installationSteps($install, $releasePath),
            ...$this->symlinkSteps($releasePath),
            ...$this->composerSteps($releasePath),
            ...$this->storageLinkSteps($releasePath),
            ...$this->generateKeySteps($install, $releasePath),
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
            'cd '.$this->server->root(),
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
                'cd '.$this->server->root(),
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
        $composerExec = $this->server->composerBin();

        if ($this->server->composer('local')) {
            $composerExec = $this->server->phpBin().' '.$this->server->composerBin();
        }

        $composerCommand = $composerExec.' install --ansi';

        if (!$this->server->composer('dev')) {
            $composerCommand .= ' --no-dev';
        }

        return [
            'cd '.$this->server->root(),
            ...$this->server->script('before-composer'),
            'cd '.$releasePath.PHP_EOL
            .$composerCommand,
            ...$this->server->script('after-composer'),
        ];
    }

    protected function storageLinkSteps(string $releasePath): array
    {
        return [
            $this->server->phpBin().' '.$releasePath.'/artisan storage:link',
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

        return [
            'cd '.$this->server->root(),
            $env,
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
            'cd '.$this->server->root(),
            ...$this->server->script('before-install'),
            $install
                ? 'mv '.$releasePath.'/storage '.$this->server->path('storage')
                : 'rm -rf '.$releasePath.'/storage',
            ...$this->server->script('after-install'),
        ];
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
            'cd '.$this->server->root(),
            ...$this->server->script('before-symlinks'),
            'ln -nfs '.$this->server->path('storage').' '.$releasePath.'/storage',
            'ln -nfs '.$this->server->path('env').' '.$releasePath.'/.env',
            ...$this->server->script('after-symlinks'),
        ];
    }

    protected function generateKeySteps(bool $install, string $releasePath): array
    {
        return $install
            ? [
                $this->server->phpBin().' '.$releasePath.'/artisan key:generate',
            ]
            : [];
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
            'cd '.$this->server->root(),
            ...$this->server->script('before-migrate'),
            $this->server->phpBin().' '.$releasePath.'/artisan migrate --force',
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

        $assets = $this->getAssetCommands($this->server->assets(), $releasePath);

        $commands = [
            'printf "\033c"',
            ...$this->server->script('before-assets'),
            'echo "Copying assets..."',
            ...$assets,
            ...$this->server->script('after-assets'),
        ];

        return new Task(implode(PHP_EOL, $commands));
    }

    protected function getAssetCommands(array $assets, string $releasePath): array
    {
        $commands = [];

        foreach ($assets as $asset => $target) {
            $target = Str::startsWith($target, '/') ? $target : '/'.$target;

            $commands[] = 'scp -P'
                .$this->server->port() // port
                .' -r '.$asset.' ' // local asset
                .$this->server->user().'@'.$this->server->host() // remote
                .':'.$releasePath.$target; // remote path
        }

        return $commands;
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
            'cd '.$this->server->root(),
            ...$this->server->script('before-live'),
            'ln -nfs '.$releasePath.' '.$this->server->path('serve'),
            ...$this->server->script('after-live'),
            'printf "\033c"',
        ];

        return new Task(implode(PHP_EOL, $commands), $this->server);
    }
}
