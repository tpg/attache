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
        $this->releases = $this->getReleases();

        if (count($this->releases)) {
            $this->active = $this->getActiveRelease();
        }

        return $this;
    }

    public function hasInstallation(): bool
    {
        $releases = $this->getReleases();
        //$releases = $this->getReleasesFromOutput($output);

        return count($releases) > 0;
    }

    /**
     * Get release data from the server by running a simple `ls` command.
     *
     * @return array
     */
    protected function getReleases(): array
    {
        $command = 'ls '.$this->server->path('releases');

        $task = new Task($command, $this->server);

        $data = $this->run($task);

        return array_filter(explode(PHP_EOL, $data), static function ($release) {
            return $release !== '';
        });
    }

    protected function getActiveRelease(): ?string
    {
        $command = 'ls -la '.$this->server->root();

        $task = new Task($command, $this->server);

        $data = $this->run($task);

        preg_match('/'.$this->server->path('serve', false).'.*\/(?<id>.+)/', $data, $matches);

        return Arr::get($matches, 'id');
    }

    protected function run(Task $task): string
    {
        $data = null;

        (new Ssh($task))->run(static function (Task $task, $output) use (&$data) {
            $data = $output;
        });

        return $data;
    }

    /**
     * Validate that the output matches the correct format.
     *
     * @param string $output
     * @return bool
     */
    protected function validateOutput(string $output): bool
    {
        return true;
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
