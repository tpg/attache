<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Collection;
use TPG\Attache\Contracts\ReleaseManagerContract;
use TPG\Attache\Contracts\ServerContract;
use TPG\Attache\Contracts\TargetContract;
use TPG\Attache\Contracts\TaskRunnerContract;
use TPG\Attache\Exceptions\ProcessException;
use TPG\Attache\Targets\Ssh;

class ReleaseManager implements ReleaseManagerContract
{
    protected TargetContract $target;

    protected ServerContract $server;

    protected TaskRunnerContract $runner;

    public function __construct(ServerContract $server)
    {
        $this->server = $server;
        $this->target = new Ssh($server);
        $this->runner = new TaskRunner($this->target);
    }

    public function setTarget(TargetContract $target): void
    {
        $this->target = $target;
    }

    public function setTaskRunner(TaskRunnerContract $runner): void
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
        $releases = $this->list();

        dd($releases);

        return false;
    }
}
