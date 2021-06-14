<?php

declare(strict_types=1);

namespace TPG\Attache;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Console\Application as ConsoleApplication;
use TPG\Attache\Commands\DeployCommand;
use TPG\Attache\Commands\InitCommand;
use TPG\Attache\Commands\ReleasesActivateCommand;
use TPG\Attache\Commands\ReleasesListCommand;
use TPG\Attache\Commands\ReleasesPruneCommand;
use TPG\Attache\Commands\ReleasesRollbackCommand;
use TPG\Attache\Commands\ServersListCommand;

class Application
{
    protected string $name = 'Attaché 2';

    protected string $version = '0.0.1';

    protected array $commands = [
        InitCommand::class,
        ServersListCommand::class,
        ReleasesListCommand::class,
        ReleasesActivateCommand::class,
        ReleasesRollbackCommand::class,
        ReleasesPruneCommand::class,
        DeployCommand::class,
    ];
    private ConsoleApplication $consoleApplication;

    private Filesystem $filesystem;

    public function __construct()
    {
        $this->consoleApplication = new ConsoleApplication($this->name, $this->version);

        $this->filesystem = new Filesystem(
            new LocalFilesystemAdapter(
                getcwd(),
                linkHandling: LocalFilesystemAdapter::SKIP_LINKS
            )
        );
    }

    public function boot(): ConsoleApplication
    {
        foreach ($this->commands as $command) {
            $this->consoleApplication->add(new $command($this->filesystem));
        }

        return $this->consoleApplication;
    }
}
