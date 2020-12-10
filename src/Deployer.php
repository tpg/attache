<?php

declare(strict_types=1);

namespace TPG\Attache;

use TPG\Attache\Contracts\DeployerContract;
use TPG\Attache\Contracts\ServerContract;

class Deployer implements DeployerContract
{
    /**
     * @var ServerContract
     */
    protected ServerContract $server;

    public function __construct(ServerContract $server)
    {
        $this->server = $server;
    }

    public function deploy(string $releaseId, callable $callback = null): void
    {
        // TODO: Implement deploy() method.
    }

    public function install(string $releaseId, string $env, callable $callback = null): void
    {
        // TODO: Implement install() method.
    }
}
