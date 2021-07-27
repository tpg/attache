<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TPG\Attache\Contracts\ServerInterface;

class Server implements ServerInterface
{
    protected string $name;
    protected array $connection = [
        'host' => null,
        'port' => 22,
        'username' => null,
        'root' => null,
    ];
    protected array $git = [
        'repository' => null,
        'depth' => 1,
        'branch' => 'master',
    ];
    protected array $paths = [
        'releases' => 'releases',
        'serve' => 'live',
        'storage' => 'storage',
        'env' => '.env',
    ];
    protected array $php = [
        'bin' => 'php',
    ];

    protected array $assets = [];

    protected array $settings = [
        'copyUtility' => 'scp',
    ];
    protected array $scripts = [];

    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->updateSettings(Arr::get($config, 'settings', []));
        $this->setConnection($config);
        $this->setGit(Arr::get($config, 'git', []));
        $this->setPaths(Arr::get($config, 'paths', []));
        $this->setPhp(Arr::get($config, 'php', []));
        $this->setSteps(Arr::get($config, 'steps', []));
    }

    protected function updateSettings(array $settings): self
    {
        $this->settings = array_merge(
            $this->settings,
            Arr::only($settings, array_keys($this->settings))
        );

        return $this;
    }

    public function settings(string $key): mixed
    {
        return Arr::get($this->settings, $key);
    }

    public function setConnection(array $connection): self
    {
        $this->connection = array_merge(
            $this->connection,
            Arr::only($connection, array_keys($this->connection))
        );

        return $this;
    }

    public function setGit(array $git): self
    {
        $this->git = array_merge(
            $this->git,
            Arr::only($git, array_keys($this->git))
        );

        return $this;
    }

    public function setPaths(array $paths): self
    {
        $this->paths = array_merge(
            $this->paths,
            Arr::only($paths, array_keys($this->paths))
        );

        return $this;
    }

    public function setPhp(array $php): self
    {
        $this->php = array_merge(
            $this->php,
            Arr::only($php, array_keys($this->php))
        );

        return $this;
    }

    public function setAssets(array $assets): self
    {
        $this->assets = array_merge(
            $this->assets,
            $assets
        );

        return $this;
    }

    public function setSteps(array $scripts): self
    {
        $this->scripts = array_merge_recursive($this->scripts, $scripts);

        return $this;
    }

    public function assets(): array
    {
        return $this->assets;
    }

    public function step(string $step): array
    {
        return Arr::get($this->scripts, $step, []);
    }

    public function scripts(string $step, string $subStep): array
    {
        return Arr::get($this->step($step), $subStep, []);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function rootPath(bool $trailingSlash = false): string
    {
        $root = Arr::get($this->connection, 'root', '');

        if ($trailingSlash && ! Str::endsWith($root, '/')) {
            return $root.'/';
        }

        return Str::endsWith($root, '/')
            ? substr($root, 0, -1)
            : $root;
    }

    public function path(string $key, bool $absolute = true): string
    {
        $path = Arr::get($this->paths, $key);

        if (! $path) {
            $path = $key;
        }

        return $absolute ? $this->rootPath(true).$path : $path;
    }

    public function phpBin(): string
    {
        return Arr::get($this->php, 'bin', 'php');
    }

    public function composerBin(): string
    {
        return $this->rootPath(true).'composer.phar';
    }

    public function hostString(): string
    {
        return $this->host().' -p'.$this->port();
    }

    public function connectionString(): string
    {
        return $this->username().'@'.$this->host().' -p'.$this->port();
    }

    public function username(): string
    {
        return Arr::get($this->connection, 'username');
    }

    public function host(): string
    {
        return Arr::get($this->connection, 'host');
    }

    public function port(): int
    {
        return Arr::get($this->connection, 'port');
    }

    public function cloneString(?string $target = null): string
    {
        return trim('-b '.$this->branch().' --depth='.$this->depth().' "'.$this->repository().'" '.$target);
    }

    protected function branch(): string
    {
        return Arr::get($this->git, 'branch');
    }

    protected function depth(): int
    {
        return Arr::get($this->git, 'depth');
    }

    protected function repository(): string
    {
        return Arr::get($this->git, 'repository');
    }
}
