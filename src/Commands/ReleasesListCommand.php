<?php
/**
 * attache releases:list <server>.
 *
 * List all the current releases installed on the specified server.
 */

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Carbon\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\Release;
use TPG\Attache\ReleaseManager;

class ReleasesListCommand extends Command
{
    protected string $name = 'releases:list';
    protected string $description = 'List the currently installed releases on the specified server';
    protected bool $requireConfig = true;
    protected bool $requireServer = true;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->setReleaseManager(new ReleaseManager($this->server));
    }

    protected function fire(): int
    {
        $release = $this->releaseManager->fetch();

        $this->displayReleases($release);

        return 0;
    }

    protected function displayReleases(Release $release): void
    {
        $table = new Table($this->output);
        $table->setHeaders([
            'ID',
            'Release Date',
        ])->setRows(
            collect($release->available())->map(fn ($available) => [
                $this->releaseId($available, $release->active()),
                Carbon::createFromFormat('YmdHis', $available)->format('d F Y H:i:s'),
            ])->toArray()
        );

        $table->setStyle('box')->render();
    }

    protected function releaseId(string $releaseId, string $active): TableCell
    {
        $prefix = '  ';

        if ($releaseId === $active) {
            $prefix = '* ';
        }

        return new TableCell(
            $prefix.$releaseId,
            $releaseId === $active ? [
                'style' => new TableCellStyle([
                    'bg' => 'green',
                ]),
            ] : []
        );
    }
}
