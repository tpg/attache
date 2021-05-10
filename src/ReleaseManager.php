<?php

declare(strict_types=1);

namespace TPG\Attache;

use Closure;
use Illuminate\Support\Arr;
use Laravel\Envoy\SSH;
use Laravel\Envoy\Task;
use TPG\Attache\Contracts\ReleaseManagerInterface;
use TPG\Attache\Contracts\ServerInterface;
use TPG\Attache\Exceptions\ConfigurationException;

class ReleaseManager implements ReleaseManagerInterface
{
    /**
     * ReleaseManager constructor.
     */
    public function __construct(protected ServerInterface $server)
    {
    }

    public function fetch(): Release
    {
        $task = $this->getTask([
            'echo ATTACHE-SCRIPT',
            'cd '.$this->server->path('releases'),
            'ls',
            'echo ATTACHE-DELIM',
            'cd '.$this->server->rootPath(),
            'ls -la '.$this->server->path('serve', false),
            'echo ATTACHE-SCRIPT',
        ]);

        $data = '';
        $this->runTask($task, function ($output) use (&$data) {
            $data .= $output;
        });

        return (new Release())->parse($data);
    }

    public function activate(?string $releaseId = null): string
    {
        $release = $this->fetch();

        $releaseId ??= Arr::last($release->available());

        if (! in_array($releaseId, $release->available(), true)) {
            throw new ConfigurationException('Release ID '.$releaseId.' not found on '.$this->server->name());
        }

        $task = $this->getTask([
            'ln -nfs '.$this->server->path('releases').'/'.$releaseId.' '.$this->server->path('serve'),
        ]);

        $this->runTask($task);

        return $releaseId;
    }

    public function prune(int $retain = 2, ?Closure $closure = null): void
    {
        $release = $this->fetch();

        $command = 'rm -rf '.$this->server->path('releases').'/';

        if ($retain < 1) {
            $retain = 1;
        }

        for ($i = 0; $i < count($release->available()) - ($retain); $i++) {
            $id = $release->available()[$i];

            $task = $this->getTask([
                $command.$id,
            ]);
            $this->runTask($task, function ($output) use ($closure, $id) {
                if ($closure) {
                    $closure($id, $output);
                }
            });
        }
    }

    protected function getTask(array $commands): Task
    {
        return new Task(
            [$this->server->username().'@'.$this->server->hostString()],
            '',
            implode(PHP_EOL, $commands),
        );
    }

    protected function runTask(Task $task, ?Closure $closure = null): void
    {
        $ssh = new SSH();
        $ssh->run($task, function ($type, $host, $output) use ($closure) {
            if ($closure) {
                $closure($output);
            }
        });
    }
}
