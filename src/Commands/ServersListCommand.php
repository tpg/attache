<?php
/**
 * attache servers:list.
 *
 * List all the configured servers.
 */

declare(strict_types=1);

namespace TPG\Attache\Commands;

class ServersListCommand extends Command
{
    protected string $name = 'servers:list';
    protected string $description = 'List the configured servers';
    protected bool $requireConfig = true;

    protected function fire(): int
    {
        return 0;
    }
}
