<?php

namespace TPG\Attache;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use TPG\Attache\Exceptions\ConfigurationException;

class ConfigurationProvider
{
    protected array $servers = [];

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
        if (!file_exists($filename)) {
            throw new FileNotFoundException('Cannot find config file '.$filename);
        }

        try {
            $config = json_decode(file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);

            $this->loadServers(Arr::get($config, 'servers'));

        } catch (\Exception $e) {
            throw new \JsonException('Unable to read config file.'."\n".$e->getMessage());
            exit(1);
        }
    }

    protected function loadServers(array $servers): void
    {
        $this->validateServers($servers);

        $this->servers = (new Collection($servers))->keyBy('name')->toArray();
    }

    protected function validateServers(array $servers): void
    {
        foreach ($servers as $server) {
            if (!Arr::has($server, ['name', 'host', 'port', 'user', 'root', 'branch'])) {
                throw new ConfigurationException('Missing server configuration key');
            }
        }
    }

    public function servers(): array
    {
        return $this->servers;
    }

    public function server($key): array
    {
        $server = Arr::get($this->servers, $key);
        if (!$server) {
            throw new ConfigurationException('Unknown server with key '.$key);
        }

        return $server;
    }

    public function serverConnectionString($key): string
    {
        $server = $this->server($key);

        return $server['user'].'@'.$server['host'].' -p'.$server['port'];
    }
}
