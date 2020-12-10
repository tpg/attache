<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Symfony\Component\Console\Input\InputOption;
use TPG\Attache\Contracts\DeployerContract;
use TPG\Attache\Contracts\ReleaseManagerContract;
use TPG\Attache\Contracts\TaskContract;
use TPG\Attache\Deployer;
use TPG\Attache\ReleaseManager;

class InstallCommand extends Command
{
    protected ReleaseManagerContract $releaseManager;
    protected DeployerContract $deployer;

    protected function configure(): void
    {
        $this->setName('install')
            ->addOption(
                'branch',
                'b',
                InputOption::VALUE_REQUIRED,
                'Override the configured branch')
            ->addOption(
                'env',
                'e',
                InputOption::VALUE_REQUIRED,
                'The .env file to install', '.env.example')
            ->requiresConfig()
            ->requiresServer();
    }

    public function setReleaseManager(ReleaseManagerContract $releaseManager): void
    {
        $this->releaseManager = $releaseManager;
    }

    public function setDeployer(DeployerContract $deployer): void
    {
        $this->deployer = $deployer;
    }

    protected function fire(): int
    {
        $releaseManager = $this->releaseManager ?? new ReleaseManager($this->server(), $this->printer);
        $deployer = $this->deployer ?? new Deployer($this->server());

        if ($releaseManager->hasInstallation()) {
            $this->printer->error($this->printer->friendlyErrorMessage('An installation already exists on '.$this->server()->name()));
            exit(13);
        }

        $env = $this->getEnv($this->option('env'));

        $this->deployer->install($this->getReleaseId(), $env, function (TaskContract $task, $percentage) {
            // install progress...
        });

        return 0;
    }

    protected function getEnv(?string $filename = null): string
    {
        if ($filename && ! file_exists($filename)) {
            $this->printer->error($filename.' does not exist.');
            exit(1);
        }

        if (! $filename) {
            $filename = '.env.example';
        }

        try {
            return file_get_contents($filename);
        } catch (\Exception $e) {
            $this->printer->error($filename.' does not exist');
            exit(1);
        }
    }

    protected function getReleaseId(): string
    {
        return date('YmdHis');
    }
}
