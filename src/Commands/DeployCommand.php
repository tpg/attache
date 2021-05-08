<?php
/**
 * attache deploy <server>
 *
 * Deploy the current project to the given server.
 * Servers are defined in the configuration file.
 */

declare(strict_types=1);

namespace TPG\Attache\Commands;

class DeployCommand extends Command
{
    protected string $name = 'deploy';
    protected string $description = 'Deploy to the specified server';
    protected bool $requireConfig = true;
    protected bool $requireServer = true;

    protected function fire(): int
    {
        return 0;
    }
}
