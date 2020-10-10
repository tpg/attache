<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Collection;
use TPG\Attache\Targets\Target;

class ReleaseManager
{
    /**
     * @var Server
     */
    protected Server $server;
    /**
     * @var Target
     */
    protected Target $target;

    /**
     * ReleaseManager constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function setServer(Server $server): void
    {
        $this->server = $server;
    }

    public function list(): Collection
    {
        $task = new Task('ls '.$this->server->path('releases'));

        $runner = new TaskRunner($this->target);

        $runner->run([$task]);

        return collect(explode(PHP_EOL, $runner->getResults()->first()->output()))
            ->filter(fn ($release) => $release !== '');
    }

    public function active(): string
    {
        $task = new Task('ls '.$this->server->path('serve').' -la');

        $runner = new TaskRunner($this->target);

        $runner->run([$task]);

        $regex = '/->\s.*\/(?<release>.+)$/';

        preg_match($regex, $runner->getResults()->first()->output(), $matches);

        return $matches['release'];
    }

    public function setTarget(Target $target): void
    {
        $this->target = $target;
    }
}
