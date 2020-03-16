<?php

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Server
{
    protected array $config = [
        'name' => null,
        'branch' => 'master',
        'host' => null,
        'port' => 22,
        'user' => null,
        'root' => null,
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
        ]
    ];

    /**
     * Server constructor.
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        if ($config) {
            $this->setServer($config);
        }
    }

    public function setServer(array $config): self
    {
        $this->config = array_replace_recursive($this->config, $config);
        return $this;
    }

    public function config($key = null)
    {
        return Arr::get($this->config, $key);
    }

    public function name()
    {
        return Arr::get($this->config, 'name');
    }

    public function host()
    {
        return Arr::get($this->config, 'host');
    }

    public function port()
    {
        return Arr::get($this->config, 'port', 22);
    }

    public function user()
    {
        return Arr::get($this->config, 'user');
    }

    public function root(bool $trailingSlash = false)
    {
        $root = Arr::get($this->config, 'root');

        if ($trailingSlash && ! Str::endsWith($root, '/')) {
            return $root.'/';
        }

        return Str::endsWith($root, '/')
            ? substr($root, 0, -1)
            : $root;
    }

    public function branch(): string
    {
        return Arr::get($this->config, 'branch');
    }

    public function path(string $key, bool $absolute = true)
    {
        $path = Arr::get($this->config, 'paths.'.$key);

        if (! $path) {
            $path = $key;
        }

        return $absolute
            ? $this->root(true).$path
            : $path;
    }

    public function migrate(): bool
    {
        return Arr::get($this->config, 'migrate', false);
    }

    public function phpBin(): string
    {
        return Arr::get($this->config, 'php.bin', 'php');
    }

    public function composer($key = null)
    {
        $key = 'composer' . ($key ? '.'.$key : null);

        return Arr::get($this->config, $key);
    }

    public function composerBin(): string
    {
        $bin = Arr::get($this->config, 'composer.bin');
        if (Arr::get($this->config, 'composer.local', false)) {
            $bin = $this->root().'/'.$bin;
        }

        return $bin;
    }
}
