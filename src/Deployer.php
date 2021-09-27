<?php

namespace TPG\Attache;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use TPG\Attache\Exceptions\ProcessException;

class Deployer
{
    /**
     * The default build command.
     */
    protected const BUILD_COMMAND = ['yarn prod'];

    /**
     * Process timeout in seconds.
     */
    protected const PROCESS_TIMEOUT = 3600;

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
     * @var bool
     */
    protected bool $tty = false;

    /**
     * @param  ConfigurationProvider  $config
     * @param  Server  $server
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
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
     * @param  string  $releaseId
     *
     * @throws ProcessException
     */
    public function deploy(string $releaseId): void
    {
        $tasks = $this->getTasks($releaseId);

        $this->executeTasks($tasks);
    }

    /**
     * Get the tasks to execute.
     *
     * @param  string  $releaseId
     * @return array
     */
    public function getTasks(string $releaseId): array
    {
        return [
            $this->buildTask(),
            $this->deploymentTask(
                $releaseId,
                $this->server->migrate(),
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
        $command = $this->server->script('build') ?: self::BUILD_COMMAND;

        $commands = [
            ...$this->server->script('before-build'),
            ...$command,
            ...$this->server->script('after-build'),
        ];

        return new Task(implode(PHP_EOL, $commands));
    }

    /**
     * The deployment task.
     *
     * @param  string  $releaseId
     * @param  bool  $migrate
     * @param  bool  $install
     * @return Task
     */
    protected function deploymentTask(string $releaseId, $migrate = false, $install = false): Task
    {
        $releasePath = $this->releasePath($releaseId);

        $commands = array_filter([
            'printf "\033c"',
            ...$this->server->script('before-deploy', $releaseId, $this->server->root()),
            'cd '.$this->server->root(),
            ...$this->cloneSteps($releasePath),
            ...$this->getComposer($releasePath),
            ...$this->envSteps($releasePath),
            ...$this->installationSteps($install, $releasePath),
            ...$this->symlinkSteps($releasePath),
            ...$this->composerSteps($releasePath),
            ...$this->storageLinkSteps($releasePath),
            ...$this->generateKeySteps($install, $releasePath),
            ...$this->migrationSteps($migrate, $releasePath),
            ...$this->server->script('after-deploy', $releaseId, $this->server->root()),
        ], static function ($command) {
            return $command !== null;
        });

        return new Task(implode(PHP_EOL, $commands), $this->server);
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
     * Git clone steps.
     *
     * @param  string  $releasePath
     * @return array
     */
    protected function cloneSteps(string $releasePath): array
    {
        return [
            ...$this->server->script('before-clone', $this->server->root()),
            'cd '.$this->server->root(),
            'git clone -b '.$this->server->branch().' --depth=1 '.$this->config->repository().' '.$releasePath,
            ...$this->server->script('after-clone', $this->server->root()),
        ];
    }

    /**
     * Get a copy of composer.
     *
     * @param  string  $releasePath
     * @return array
     */
    protected function getComposer(string $releasePath): array
    {
        if ($this->server->composer('local')) {
            return [
                ...$this->server->script('before-prepcomposer', $this->server->root()),
                'cd '.$this->server->root(),
                'if test ! -f "'.$this->server->composerBin().'"; then',
                'curl -sS https://getcomposer.org/installer -o composer-installer.php',
                $this->server->phpBin().' composer-installer.php --install-dir='.$this->server->root().' --filename='.$this->server->composer('bin'),
                'rm composer-installer.php',
                'else',
                $this->server->composerBin().' self-update',
                'fi',
                ...$this->server->script('after-prepcomposer', $this->server->root()),
            ];
        }

        return [];
    }

    /**
     * Steps to place a new `.env` file.
     *
     * @param  string  $releasePath
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
     * @param  bool  $install
     * @param  string  $releasePath
     * @return array
     */
    protected function installationSteps(bool $install, string $releasePath): array
    {
        return [
            ...$this->server->script('before-install', $this->server->root()),
            'cd '.$this->server->root(),
            $install
                ? 'mv '.$releasePath.'/storage '.$this->server->path('storage')
                : 'rm -rf '.$releasePath.'/storage',
            ...$this->server->script('after-install', $this->server->root()),
        ];
    }

    /**
     * The symbolic link steps.
     *
     * @param  string  $releasePath
     * @return array
     */
    protected function symlinkSteps(string $releasePath): array
    {
        return [
            ...$this->server->script('before-symlinks', $this->server->root()),
            'ln -nfs '.$this->server->path('storage').' '.$releasePath.'/storage',
            'ln -nfs '.$this->server->path('env').' '.$releasePath.'/.env',
            ...$this->server->script('after-symlinks', $this->server->root()),
        ];
    }

    /**
     * The composer install steps.
     *
     * @param  string  $releasePath
     * @return array
     */
    protected function composerSteps(string $releasePath): array
    {
        $composerExec = $this->server->composerBin();

        if ($this->server->composer('local')) {
            $composerExec = $this->server->phpBin().' '.$this->server->composerBin();
        }

        $composerCommand = $composerExec.' install --ansi';

        if (! $this->server->composer('dev')) {
            $composerCommand .= ' --no-dev';
        }

        return [
            ...$this->server->script('before-composer', $this->server->root()),
            'cd '.$releasePath.PHP_EOL
            .$composerCommand,
            ...$this->server->script('after-composer', $this->server->root()),
        ];
    }

    /**
     * Get the steps for creating the storage links through Artisan.
     *
     * @param  string  $releasePath
     * @return array|string[]
     */
    protected function storageLinkSteps(string $releasePath): array
    {
        return [
            $this->server->phpBin().' '.$releasePath.'/artisan storage:link',
        ];
    }

    /**
     * Generate the application encryption key through Artisan.
     *
     * @param  bool  $install
     * @param  string  $releasePath
     * @return array|string[]
     */
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
     * @param  bool  $migrate
     * @param  string  $releasePath
     * @return array
     */
    protected function migrationSteps(bool $migrate, string $releasePath): array
    {
        return $migrate ? [
            ...$this->server->script('before-migrate', $this->server->root()),
            'cd '.$this->server->root(),
            $this->server->phpBin().' '.$releasePath.'/artisan migrate --force',
            ...$this->server->script('after-migrate', $this->server->root()),
        ] : [];
    }

    /**
     * The asset migration task.
     *
     * @param  string  $releaseId
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

    /**
     * Get the asset installation commands.
     *
     * @param  array  $assets
     * @param  string  $releasePath
     * @return array
     */
    protected function getAssetCommands(array $assets, string $releasePath): array
    {
        $commands = [];

        foreach ($assets as $asset => $target) {
            if (! file_exists($asset)) {
                $commands[] = 'echo '.$asset . ' not found. Skipping.';
                continue;
            }

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
     * @param  string  $releaseId
     * @return Task
     */
    protected function liveTask(string $releaseId): Task
    {
        $releasePath = $this->server->path('releases').'/'.$releaseId;

        $commands = [
            'cd '.$this->server->root(),
            ...$this->server->script('before-live', $this->server->root()),
            'ln -nfs '.$releasePath.' '.$this->server->path('serve'),
            ...$this->server->script('after-live', $this->server->root()),
            'printf "\033c"',
        ];

        return new Task(implode(PHP_EOL, $commands), $this->server);
    }

    /**
     * Execute an array of tasks.
     *
     * @param  Task[]  $tasks
     *
     * @throws ProcessException
     */
    protected function executeTasks(array $tasks): void
    {
        foreach ($tasks as $task) {
            if ($task->server()) {
                $this->executeTaskOnServer($task);
            } else {
                $this->executeTaskLocally($task);
            }
        }
    }

    /**
     * Execute tasks on the server through an `Ssh` instance.
     *
     * @param  Task  $task
     *
     * @throws ProcessException
     */
    protected function executeTaskOnServer(Task $task): void
    {
        $code = (new Ssh($task))->tty()->run(function ($task, $output) {
            $this->getOutput()->writeln($output);
        }, self::PROCESS_TIMEOUT);

        if ($code !== 0) {
            $this->failProcess();
        }
    }

    /**
     * Get the current OutputInterface.
     *
     * @return OutputInterface
     */
    protected function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * End the current execution and throw an exception.
     *
     * @param  string|null  $err
     *
     * @throws ProcessException
     */
    protected function failProcess(string $err = null): void
    {
        throw new ProcessException($err ?: 'Failed to execute one or more tasks');
    }

    /**
     * Execute tasks on the local environment.
     *
     * @param  Task  $task
     *
     * @throws ProcessException
     */
    protected function executeTaskLocally(Task $task): void
    {
        $process = Process::fromShellCommandline($task->getBashScript())->disableOutput();
        $process->setTimeout(self::PROCESS_TIMEOUT);

        if ($this->tty) {
            $process->setTty(Process::isTtySupported());
        }

        $process
            ->run(function ($type, $output) {
                if ($type === Process::ERR) {
                    $this->failProcess($output);
                }
                $this->getOutput()->writeln($output);
            });

        if ($process->getExitCode() !== 0) {
            $this->failProcess();
        }
    }

    /**
     * Install a release.
     *
     * @param  string  $releaseId
     * @param  string|null  $env
     *
     * @throws ProcessException
     */
    public function install(string $releaseId, string $env = null): void
    {
        $this->installEnv = $env;

        $tasks = $this->getInstallationTasks($releaseId);

        $this->executeTasks($tasks);
    }

    /**
     * Get tasks needed for installation.
     *
     * @param  string  $releaseId
     * @return array
     */
    public function getInstallationTasks(string $releaseId): array
    {
        return [
            $this->buildTask(),
            $this->buildRootTask(),
            $this->deploymentTask(
                $releaseId,
                $this->server->migrate(),
                true
            ),
            $this->assetTask($releaseId),
            $this->liveTask($releaseId),
        ];
    }

    protected function buildRootTask(): Task
    {
        $commands = [
            'mkdir -p '.$this->server->path('releases'),
        ];

        return new Task(implode(PHP_EOL, $commands), $this->server);
    }

    /**
     * Set TTY use.
     *
     * @param  bool  $tty
     */
    public function tty(bool $tty = true): void
    {
        $this->tty = $tty;
    }
}
