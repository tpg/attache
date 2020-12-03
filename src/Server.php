<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TPG\Attache\Contracts\ServerContract;

class Server implements ServerContract
{
    protected string $name;
    protected array $config;

    public function __construct(string $name = null, array $config = [])
    {
        if ($name) {
            $this->setName($name);
        }

        if ($config) {
            $this->setConfig($config);
        }
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setConfig(array $config): void
    {
        $this->config = array_replace_recursive(
            $this->defaultConfiguration(),
            $config
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function host(): string
    {
        return $this->config('host');
    }

    public function port(): int
    {
        return $this->config('port');
    }

    public function user(): string
    {
        return $this->config('user');
    }

    public function branch(): string
    {
        return $this->config('branch');
    }

    public function root(bool $trailingSlash = false): string
    {
        $root = $this->config('root') ?? '';

        if ($trailingSlash && ! Str::endsWith($root, '/')) {
            return $root.'/';
        }

        return Str::endsWith($root, '/')
            ? substr($root, 0, -1)
            : $root;
    }

    public function paths(bool $absolute = true): array
    {
        return $this->config('paths');
    }

    public function path(string $key, bool $absolute = true): ?string
    {
        $path = Arr::get($this->paths($absolute), $key);

        if (! $path) {
            $path = $key;
        }

        return $absolute
            ? $this->root(true).$path
            : $path;
    }

    public function migrate(): bool
    {
        return $this->config('migrate');
    }

    public function php(): string
    {
        return $this->config('php.bin', 'php');
    }

    public function composer(): string
    {
        $bin = $this->config('composer.bin', 'composer');

        return $this->config('composer.local', false)
            ? $this->root(true).$bin
            : $bin;
    }

    public function assets(string $key = null)
    {
        $key = 'assets'.($key ? '.'.$key : null);

        return $this->config($key);
    }

    public function connectionString(): string
    {
        return $this->user().'@'.$this->host().' -p'.$this->port();
    }

    public function config($key, $default = null)
    {
        return Arr::get($this->config, $key, $default);
    }

    protected function defaultConfiguration(): array
    {
        return [
            'port' => 22,
            'paths' => [
                'releases' => 'releases',
                'serve' => 'live',
                'storage' => 'storage',
                'env' => '.env',
            ],
            'php' => [
                'bin' => 'php',
            ],
            'composer' => [
                'bin' => 'composer',
                'local' => false,
            ],
            'assets' => [
                'public/js' => 'public/js',
                'public/css' => 'public/css',
                'public/mix-manifest.json' => 'public/mix-manifest.json',
            ],
            'branch' => 'master',
            'migrate' => false,
        ];
    }
}
