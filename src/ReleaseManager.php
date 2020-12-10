<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Collection;
use TPG\Attache\Contracts\PrinterContract;
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

    protected ?PrinterContract $printer;

    public function __construct(ServerContract $server, PrinterContract $printer = null)
    {
        $this->server = $server;
        $this->target = new Ssh($server);
        $this->runner = new TaskRunner($this->target);
        $this->printer = $printer;
    }

    public function setTarget(TargetContract $target): void
    {
        $this->target = $target;
    }

    public function setTaskRunner(TaskRunnerContract $runner): void
    {
        $this->runner = $runner;
    }

    public function hasInstallation(): bool
    {
        $task = new Task('ls '.$this->server->path('releases'));

        $this->runner->run([$task]);

        if ($this->runner->hasError()) {
            return false;
        }

        return false;
    }

    public function list(): Collection
    {
        $task = new Task('ls '.$this->server->path('releases'));

        $this->runner->run([$task]);

        if ($this->runner->hasError()) {
            $this->printer->fromResult($this->runner->errors()->first());
            exit(12);
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

        if (! $matches) {
            $this->printer->error($this->printer->friendlyErrorMessage('installation'));
            exit(13);
        }

        return $matches['release'];
    }

    public function activate(string $release): bool
    {
        $releases = $this->list();

        return false;
    }
}
