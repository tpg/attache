<?php
/**
 * attache releases:list <server>.
 *
 * List all the current releases installed on the specified server.
 */

declare(strict_types=1);

namespace TPG\Attache\Commands;

class ReleasesListCommand extends Command
{
    protected string $name = 'releases:list';
    protected string $description = 'List the currently installed releases on the specified server';
    protected bool $requireConfig = true;
    protected bool $requireServer = true;

    protected function fire(): int
    {
        return 0;
    }
}
