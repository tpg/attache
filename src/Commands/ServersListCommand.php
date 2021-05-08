<?php
/**
 * attache servers:list.
 *
 * List all the configured servers.
 */

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use TPG\Attache\Server;

class ServersListCommand extends Command
{
    protected string $name = 'servers:list';
    protected string $description = 'List the configured servers';
    protected bool $requireConfig = true;

    protected function fire(): int
    {
        $servers = $this->configurationProvider->servers();

        $this->displayServers($servers);

        return 0;
    }

    protected function displayServers(Collection $servers): void
    {
        $table = new Table($this->output);
        $table->setHeaders([
            'Name',
            'Host',
            'Username',
            'Port',
        ])->setRows(
            $servers->map(fn (Server $server) => [
                new TableCell(
                    $server->name(),
                    [
                        'style' => new TableCellStyle([
                            'bg' => 'green',
                        ]),
                    ]
                ),
                $server->host(),
                $server->username(),
                $server->port(),
            ])->toArray());

        $table->setStyle('box')->render();
    }
}
