<?php

namespace TPG\Attache;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use TPG\Attache\Exceptions\ConfigurationException;

class ConfigurationProvider
{
    /**
     * @var Collection
     */
    protected Collection $servers;

    /**
     * @var string
     */
    protected string $repository;

    /**
     * @var string|null
     */
    protected ?string $default = null;

    /**
     * @param string $filename
     * @throws FileNotFoundException
     * @throws \JsonException
     */
    public function __construct(string $filename = null)
    {
        if ($filename) {
            $this->loadConfigFile($filename);
        }
    }

    /**
     * Load the configuration file.
     *
     * @param string $filename
     * @throws FileNotFoundException
     * @throws \JsonException
     */
    public function loadConfigFile(string $filename): void
    {
        if (! file_exists($filename)) {
            throw new FileNotFoundException('Cannot find config file '.$filename);
        }

        try {
            $this->setConfig(file_get_contents($filename));
        } catch (\Exception $e) {
            throw new \JsonException('Unable to read config.'."\n".$e->getMessage());
            exit(1);
        }
    }

    /**
     * Set the configuration.
     *
     * @param string|array $config
     * @throws ConfigurationException
     */
    public function setConfig($config): void
    {
        if (is_string($config)) {
            $config = json_decode($config, true, 512, JSON_THROW_ON_ERROR);
        }

        $this->repository = Arr::get($config, 'repository');
        $this->default = Arr::get($config, 'default');

        $servers = Arr::get($config, 'servers');
        if (!$servers) {
            throw new ConfigurationException('No servers have been configured');
        }

        $this->loadServers($servers, Arr::get($config, 'common', []));
    }

    /**
     * Load the servers from the current configuration.
     *
     * @param array $servers
     * @param array $common
     */
    protected function loadServers(array $servers, array $common): void
    {
        $this->servers = collect(array_map(function ($server) use ($common) {
            $config = array_replace_recursive($common, $server);

            $this->validateServer($config);

            return new Server($config);
        }, $servers))->keyBy(function ($item) {
            return $item->name();
        });
    }

    /**
     * Validate the provided server configuration.
     *
     * @param array $config
     * @throws ConfigurationException
     * @todo Better validation is required here. We'll need to validate the values as well and not just that the key exists.
     */
    protected function validateServer(array $config): void
    {
        if (! Arr::has($config, ['name', 'host', 'port', 'user', 'root', 'branch'])) {
            throw new ConfigurationException('Missing server configuration key');
        }
    }

    /**
     * Get the configured repository URL.
     *
     * @return string
     */
    public function repository(): string
    {
        return $this->repository;
    }

    /**
     * Get a collection of configured servers.
     *
     * @return Collection
     */
    public function servers(): Collection
    {
        return $this->servers;
    }

    /**
     * Get a single Server by its name.
     *
     * @param string|null $key
     * @return Server
     * @throws ConfigurationException
     */
    public function server(?string $key = null): Server
    {
        if (! $key && ! $this->default) {
            throw new ConfigurationException('No server key provided and no default specified');
        }

        if (! $key) {
            $key = $this->default;
        }

        if (! $this->servers->has($key)) {
            throw new ConfigurationException('Unknown server with key '.$key);
        }

        return $this->servers[$key];
    }
}
