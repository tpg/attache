<?php

namespace TPG\Attache\Console;

use Illuminate\Support\Arr;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Ssh;

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
        $server = $this->getServer();

        $command = $this->getCommand($server);

        (new Ssh($server))->run($command, function ($output) use ($server) {
            $releases = $this->getReleasesFromOutput($output);

            $active = $this->getActiveFromOutput($output);

            $this->showOutput($releases, $active);
        });

        return 0;
    }

    /**
     * Get the selected config server.
     *
     * @return array
     * @throws ConfigurationException
     */
    protected function getServer(): array
    {
        return $this->config->server($this->argument('server'));
    }

    /**
     * The command we'll execute on the server.
     *
     * @param array $server
     * @return string
     */
    protected function getCommand(array $server): string
    {
        return 'ls '.$server['root'].'/releases && ls -l '.$server['root'];
    }

    /**
     * Get an array of release IDs from the output returned after execution.
     *
     * @param array $output
     * @return array
     */
    protected function getReleasesFromOutput(array $output): array
    {
        return array_filter(
            preg_split('/\n/m', $output[0]['data']),
            fn ($release) => $release !== ''
        );
    }

    /**
     * Get a string ID of the currently active release.
     *
     * @param array $output
     * @return string
     */
    protected function getActiveFromOutput(array $output): string
    {
        preg_match('/live.*\/(?<id>.+)/', $output[1]['data'], $matches);

        return Arr::get($matches, 'id');
    }

    /**
     * Show the output on the console.
     *
     * @param array $releases
     * @param string $active
     */
    protected function showOutput(array $releases, string $active): void
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
    protected function buildTable(array $releases, string $active): array
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
