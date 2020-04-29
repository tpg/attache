<?php

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TPG\Attache\Exceptions\ServerException;

class ReleaseService
{
    /**
     * @var Server
     */
    protected Server $server;

    /**
     * @var array
     */
    protected array $releases = [];

    /**
     * @var string|null
     */
    protected ?string $active = null;

    /**
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Fetch release data from the server.
     *
     * @return $this
     * @throws ServerException
     */
    public function fetch(): self
    {
        $outputs = $this->getReleaseData();

        $this->validateOutput($outputs);

        $this->releases = $this->getReleasesFromOutput(Arr::get($outputs, 0, ''));
        if (count($this->releases)) {
            $this->active = $this->getActiveFromOutput(Arr::get($outputs, 1, ''));
        }

        return $this;
    }

    public function hasInstallation(): bool
    {
        $outputs = $this->getReleaseData();
        $releases = $this->getReleasesFromOutput($outputs[0]);

        return count($releases) > 0;
    }

    /**
     * Get release data from the server by running a simple `ls` command.
     *
     * @return array
     */
    protected function getReleaseData(): array
    {
        $command = 'ls '.$this->server->path('releases').PHP_EOL
            .'ls -l '.$this->server->root();
        $task = new Task($command, $this->server);

        $outputs = [];
        (new Ssh($task))->run(static function (Task $task, $type, $output) use (&$outputs) {
            $outputs[] = $output;
        });

        return $outputs;
    }

    /**
     * Validate that the output matches the correct format.
     *
     * @param array $output
     * @return bool
     */
    protected function validateOutput(array $output): bool
    {
        if (count($output) !== 2) {
            throw new ServerException('Failed to fetch current releases from '.$this->server->name()
                .'. Double check your configuration and try again.');
        }

        return true;
    }

    /**
     * Get an array of release IDs from the output returned after execution.
     *
     * @param string $output
     * @return array
     */
    protected function getReleasesFromOutput(string $output): array
    {
        if (! $output) {
            throw new ServerException('There was no response from '.$this->server->name()
                .'. Try again or double check your connection to the server.');
        }

        return array_filter(
            explode(PHP_EOL, $output),
            fn ($release) => $release !== '' && ! Str::contains(strtolower($release), 'no such file or directory')
        );
    }

    /**
     * Get a string ID of the currently active release.
     *
     * @param string $output
     * @return string
     */
    protected function getActiveFromOutput(string $output): ?string
    {
        preg_match('/'.$this->server->path('serve', false).'.*\/(?<id>.+)/', $output, $matches);

        return Arr::get($matches, 'id');
    }

    /**
     * Return a list of releases on the server.
     *
     * @return array
     */
    public function list(): array
    {
        return $this->releases;
    }

    /**
     * Return the active release ID.
     *
     * @return string|null
     */
    public function active(): ?string
    {
        return $this->active;
    }

    /**
     * Check if a release exists.
     *
     * @param string $id
     * @return bool
     */
    public function exists(string $id): bool
    {
        return in_array($id, $this->releases, true);
    }

    /**
     * Activate the specified release ID.
     *
     * @param string $id
     */
    public function activate(string $id): void
    {
        if ($id === 'latest') {
            $id = $this->releases[count($this->releases) - 1];
        }

        $command = 'ln -nfs '.$this->server->path('releases').'/'.$id.' '.
            $this->server->root().'/live';

        $task = new Task($command, $this->server);

        (new Ssh($task))->run();
    }

    /**
     * Delete the specified release IDs.
     *
     * @param array $ids
     */
    public function delete(array $ids): void
    {
        $commands = [];
        foreach ($ids as $id) {
            $commands[] = 'rm -rf '.$this->server->path('releases').'/'.$id;
        }

        $command = implode(' && ', $commands);

        $task = new Task($command, $this->server);

        (new Ssh($task))->run();
    }

    /**
     * Execute `artisan down` in the current release.
     */
    public function down(): void
    {
        $command = 'cd '.$this->server->path('serve').PHP_EOL
            .$this->server->phpBin().' artisan down';

        $task = new Task($command, $this->server);

        (new Ssh($task))->run();
    }

    /**
     * Execute `artisan up` in the current release.
     */
    public function up(): void
    {
        $command = 'cd '.$this->server->path('serve').PHP_EOL
            .$this->server->phpBin().' artisan up';

        $task = new Task($command, $this->server);

        (new Ssh($task))->run();
    }
}
