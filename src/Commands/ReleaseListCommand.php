<?php

declare(strict_types=1);

namespace TPG\Attache\Commands;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Style\SymfonyStyle;
use TPG\Attache\Contracts\ReleaseManagerContract;
use TPG\Attache\ReleaseManager;

class ReleaseListCommand extends Command
{
    /**
     * @var ReleaseManagerContract
     */
    protected ReleaseManagerContract $releaseManager;

    protected function configure(): void
    {
        $this->setName('release:list')
            ->setDescription('Get a list of available releases for the specified server')
            ->requiresConfig()
            ->requiresServer();
    }

    public function setReleaseManager(ReleaseManagerContract $releaseManager): void
    {
        $this->releaseManager = $releaseManager;
    }

    protected function fire(): int
    {
        $releaseManager = $this->releaseManager ?? new ReleaseManager($this->server());
        $releases = $releaseManager->list();
        $active = $releaseManager->active();

        $this->print($releases, $active);

        return 0;
    }

    protected function print(Collection $releases, string $active): void
    {
        $rows = $releases->map(function ($release) use ($active) {
            return [
                $this->info($release),
                \DateTime::createFromFormat('YmdHis', $release)->format('d F Y H:i'),
                $active === $release ? $this->info('<-- Active') : '',
            ];
        })->toArray();

        $io = new SymfonyStyle($this->input, $this->output);
        $io->table(['ID', 'Release Date', ''], $rows);
    }

    protected function info(string $message): string
    {
        return '<info>'.$message.'</info>';
    }
}
