<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;

class Server
{
    protected string $name;
    protected array $config;

    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function host(): string
    {
        return $this->config('host');
    }

    protected function config($key)
    {
        return Arr::get($this->config, $key);
    }
}
