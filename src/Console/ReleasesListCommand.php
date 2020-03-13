<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\ReleaseService;

/**
 * Class ReleasesListCommand.
 */
class ReleasesListCommand extends SymfonyCommand
{
    use Command;

    /**
     * List the releases available on the server.
     */
    protected function configure(): void
    {
        $this->setName('releases:list')
            ->setDescription('Get a list of available releases for the specified server')
            ->addArgument('server');

        $this->requiresConfig();
    }

    /**
     * @return int
     * @throws ConfigurationException
     */
    protected function fire(): int
    {
        $server = $this->config->server($this->argument('server'));

        $releaseService = (new ReleaseService($server))->fetch();

        if (! count($releaseService->list())) {
            $this->output->writeln('<error>There are no releases on '.$server->name().'</error>');
            exit(1);
        }

        $this->showOutput($releaseService->list(), $releaseService->active());

        return 0;
    }

    /**
     * Show the output on the console.
     *
     * @param array $releases
     * @param string $active
     */
    protected function showOutput(array $releases, ?string $active): void
    {
        $rows = $this->buildTable($releases, $active);

        $io = new SymfonyStyle($this->input, $this->output);
        $io->table(['ID', 'Release Date', ''], $rows);

        if (! $active || ! in_array($active, $releases, true)) {
            $this->showInactiveWarning();
        }
    }

    /**
     * Build a table of releases.
     *
     * @param array $releases
     * @param string $active
     * @return array
     */
    protected function buildTable(array $releases, ?string $active): array
    {
        $rows = [];
        foreach ($releases as $release) {
            $row = [
                '<info>'.$release.'</info>',
                $this->releaseDate($release),
            ];

            if ($release === $active) {
                $row[] = '<info><-- active</info>';
            } else {
                $row[] = '';
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Get a formatted version of the release date.
     *
     * @param string $release
     * @return string
     */
    protected function releaseDate(string $release): string
    {
        return \DateTime::createFromFormat('YmdHis', $release)->format('d F Y H:i');
    }

    /**
     * Show a warning on the console if there is no release active.
     */
    protected function showInactiveWarning(): void
    {
        $formatter = $this->getHelper('formatter');
        $block = $formatter->formatBlock('There is currently no release active', 'error');
        $this->output->write($block);
    }
}
