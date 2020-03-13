<?php

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ReleaseService
{
    /**
     * @var array
     */
    protected Server $server;

    protected array $releases = [];

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
     */
    public function fetch(): self
    {
        $command = 'ls '.$this->server->path('releases').PHP_EOL
            .'ls -l '.$this->server->root();
        $task = new Task($command, $this->server);

        $outputs = [];
        (new Ssh($task))->run(static function (Task $task, $type, $output) use (&$outputs) {
            $outputs[] = $output;
        });

        $this->releases = $this->getReleasesFromOutput($outputs[0]);
        if (count($this->releases)) {
            $this->active = $this->getActiveFromOutput($outputs[1]);
        }

        return $this;
    }

    /**
     * Get an array of release IDs from the output returned after execution.
     *
     * @param string $output
     * @return array
     */
    protected function getReleasesFromOutput(string $output): array
    {
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

    public function list(): array
    {
        return $this->releases;
    }

    public function active(): ?string
    {
        return $this->active;
    }

    public function exists(string $id): bool
    {
        return in_array($id, $this->releases, true);
    }

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

    public function installed(): bool
    {
    }

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
}
