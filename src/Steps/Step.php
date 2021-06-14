<?php

declare(strict_types=1);

namespace TPG\Attache\Steps;

use Closure;
use Illuminate\Support\Arr;
use Laravel\Envoy\SSH;
use Laravel\Envoy\Task;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\Compiler;
use TPG\Attache\Server;

abstract class Step
{
    protected const TARGET_REMOTE = 'remote';
    protected const TARGET_LOCAL = 'local';

    protected const BUILD_FOLDER = '.attache-build';

    protected OutputInterface $output;
    protected string $target = self::TARGET_LOCAL;
    protected string $key = '';

    public function __construct(
        protected string $releaseId,
        protected Filesystem $filesystem,
        protected ?Server $server = null
    ) {
    }

    public function run(Closure $callback): void
    {
        if (! $this->enabled()) {
            return;
        }

        $this->before();

        $ssh = new SSH();
        $ssh->run($this->task(), function ($type, $host, $output) use ($callback) {
            $callback($type, $host, $output, $this->message());
        });

        $this->after();
    }

    protected function enabled(): bool
    {
        return Arr::get($this->server->step($this->key), 'enabled', true);
    }

    protected function task(): Task
    {
        $compiler = new Compiler($this->server, $this->releaseId);

        $commands = [
            ...$compiler->getCompiledScripts($this->key, 'before'),
            ...$this->commands(),
            ...$compiler->getCompiledScripts($this->key, 'after'),
        ];

        return new Task(
            [
                $this->target === self::TARGET_REMOTE && $this->server
                    ? $this->server->username().'@'.$this->server->hostString()
                    : '127.0.0.1',
            ],
            '',
            implode(PHP_EOL, $commands)
        );
    }

    protected function before(): void
    {
        //
    }

    protected function after(): void
    {
        //
    }

    protected function releasePath(): string
    {
        return $this->server->path('releases').'/'.$this->releaseId;
    }

    abstract protected function commands(): array;

    protected function message(): string
    {
        return 'Deploying...';
    }
}
