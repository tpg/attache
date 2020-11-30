<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Collection;
use TPG\Attache\Contracts\ReleaseManagerContract;
use TPG\Attache\Exceptions\ProcessException;
use TPG\Attache\Targets\Ssh;
use TPG\Attache\Targets\Target;

class ReleaseManager implements ReleaseManagerContract
{
    /**
     * @var Target
     */
    protected Target $target;
    /**
     * @var Server
     */
    protected Server $server;
    /**
     * @var TaskRunner
     */
    protected TaskRunner $runner;

    /**
     * ReleaseManager constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->target = new Ssh($server);
        $this->runner = new TaskRunner($this->target);
    }

    public function setTarget(Target $target): void
    {
        $this->target = $target;
    }

    public function setTaskRunner(TaskRunner $runner): void
    {
        $this->runner = $runner;
    }

    public function list(): Collection
    {
        $task = new Task('ls '.$this->server->path('releases'));

        $this->runner->run([$task]);

        if ($this->runner->hasError()) {
            throw new ProcessException($this->runner->errors()->first()->output());
        }

        return collect(explode(PHP_EOL, $this->runner->getResults()->first()->output()))
            ->filter(fn ($release) => $release !== '');
    }

    public function active(): string
    {
        $task = new Task('ls '.$this->server->path('serve').' -la');

        $this->runner->run([$task]);

        if ($this->runner->hasError()) {
            throw new ProcessException($this->runner->errors()->first()->output());
        }

        $regex = '/->\s.*\/(?<release>.+)$/';

        preg_match($regex, $this->runner->getResults()->first()->output(), $matches);

        return $matches['release'];
    }

    public function activate(string $release): bool
    {
        //todo: Implement release activation

        return false;
    }
}
