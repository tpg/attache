<?php

namespace TPG\Attache;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use TPG\Attache\Exceptions\ConfigurationException;

class ConfigurationProvider
{
    protected Collection $servers;

    protected string $repository;

    /**
     * ConfigurationProvider constructor.
     * @param string $filename
     * @throws FileNotFoundException
     */
    public function __construct(string $filename)
    {
        $this->loadConfigFile($filename);
    }

    protected function loadConfigFile(string $filename): void
    {
        if (! file_exists($filename)) {
            throw new FileNotFoundException('Cannot find config file '.$filename);
        }

        try {
            $config = json_decode(file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);

            $this->repository = Arr::get($config, 'repository');

            $this->loadServers(Arr::get($config, 'servers'));
        } catch (\Exception $e) {
            throw new \JsonException('Unable to read config file.'."\n".$e->getMessage());
            exit(1);
        }
    }

    protected function loadServers(array $servers): void
    {
        $this->validateServers($servers);

        $this->servers = collect(array_map(function ($server) {
            return new Server($server);
        }, $servers))->keyBy(function ($item) {
            return $item->name();
        });
    }

    protected function validateServers(array $servers): void
    {
        foreach ($servers as $server) {
            if (! Arr::has($server, ['name', 'host', 'port', 'user', 'root', 'branch'])) {
                throw new ConfigurationException('Missing server configuration key');
            }
        }
    }

    public function repository(): string
    {
        return $this->repository;
    }

    public function servers(): Collection
    {
        return $this->servers;
    }

    public function server($key): Server
    {
        if (! $this->servers->has($key)) {
            throw new ConfigurationException('Unknown server with key '.$key);
        }

        return $this->servers[$key];
    }
}
