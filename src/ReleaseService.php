<?php

namespace TPG\Attache;

use Illuminate\Support\Arr;

class ReleaseService
{
    /**
     * @var array
     */
    protected array $server;

    protected array $releases;

    protected string $active;

    /**
     * Release constructor.
     * @param array $server
     */
    public function __construct(array $server)
    {
        $this->server = $server;
    }

    public function fetch(): self
    {
        $command = 'ls '.$this->server['root'].'/releases && ls -l '.$this->server['root'];

        (new Ssh($this->server))->run($command, function ($output) {
            $this->releases = $this->getReleasesFromOutput($output);
            $this->active = $this->getActiveFromOutput($output);
        });

        return $this;
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

    public function list(): array
    {
        return $this->releases;
    }

    public function active(): string
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

        $command = 'ln -nfs '.$this->server['root'].'/releases/'.$id.' '.
            $this->server['root'].'/live';

        (new Ssh($this->server))->run($command, function ($outputs) {

            //
        });
    }

    public function delete(array $ids): void
    {
        $commands = [];
        foreach ($ids as $id) {
            $commands[] = 'rm -rf '.$this->server['root'].'/releases/'.$id;
        }

        $command = implode(' && ', $commands);

        (new Ssh($this->server))->run($command, function ($outputs) use ($ids) {

            //
        });
    }
}
