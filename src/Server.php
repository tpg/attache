<?php

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Server
{
    protected array $config;

    /**
     * Server constructor.
     */
    public function __construct(array $config = null)
    {
        if ($config) {
            $this->setServer($config);
        }
    }

    public function setServer(array $config): self
    {
        $this->config = $config;

        return $this;
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
        return Arr::get($this->config, 'port');
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
        return Arr::get($this->config, 'migrate') ?: false;
    }
}
