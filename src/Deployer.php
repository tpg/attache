<?php

declare(strict_types=1);

namespace TPG\Attache;

use Closure;
use League\Flysystem\Filesystem;
use TPG\Attache\Contracts\DeployerInterface;
use TPG\Attache\Steps\Assets;
use TPG\Attache\Steps\Build;
use TPG\Attache\Steps\Dependencies;
use TPG\Attache\Steps\Install;
use TPG\Attache\Steps\Live;

class Deployer implements DeployerInterface
{
    public function __construct(protected Filesystem $filesystem, protected Server $server)
    {
    }

    public function deploy(string $releaseId, Closure $callback): bool
    {
        $steps = [
            Build::class,
            Install::class,
            Dependencies::class,
            Assets::class,
            Live::class,
        ];

        foreach ($steps as $step) {
            $instance = new $step($releaseId, $this->filesystem, $this->server);
            $instance->run($callback);
        }

        return true;
    }
}
