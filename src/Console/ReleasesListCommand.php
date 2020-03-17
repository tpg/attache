<?php

namespace TPG\Attache\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\ReleaseService;
use TPG\Attache\Server;

/**
 * Class ReleasesListCommand.
 */
class ReleasesListCommand extends Command
{
    /**
     * @var ReleaseService
     */
    protected ?ReleaseService $releaseService;

    /**
     * @param string|null $name
     * @param ConfigurationProvider|null $configurationProvider
     * @param ReleaseService|null $releaseService
     */
    public function __construct(string $name = null, ?ConfigurationProvider $configurationProvider = null, ?ReleaseService $releaseService = null)
    {
        parent::__construct($name);

        $this->config = $configurationProvider;
        $this->releaseService = $releaseService;
    }

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this->setName('releases:list')
            ->setDescription('Get a list of available releases for the specified server')
            ->requiresServer()
            ->requiresConfig();
    }

    /**
     * Get a list of releases on the server.
     *
     * @return int
     */
    protected function fire(): int
    {
        $releaseService = $this->getReleaseService($this->server);

        if (! count($releaseService->list())) {
            $this->output->writeln('<error>There appears to be no releases on '.$this->server->name().'</error>');
            exit(1);
        }

        $this->showOutput($releaseService->list(), $releaseService->active());

        return 0;
    }

    /**
     * Get an instance of ReleaseService.
     *
     * @param Server $server
     * @return ReleaseService
     */
    protected function getReleaseService(Server $server): ReleaseService
    {
        if (! $this->releaseService) {
            return (new ReleaseService($server))->fetch();
        }

        return $this->releaseService;
    }

    /**
     * Display the output on the console.
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
