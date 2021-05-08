<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Flysystem\Filesystem;
use TPG\Attache\Contracts\ConfigurationProviderInterface;
use TPG\Attache\Exceptions\ConfigurationException;

class ConfigurationProvider implements ConfigurationProviderInterface
{
    protected Collection $servers;

    /**
     * ConfigurationProvider constructor.
     */
    public function __construct(protected Filesystem $filesystem, string $filename = null)
    {
        if ($filename) {
            $this->loadConfigFile($filename);
        }
    }

    public function loadConfigFile(string $filename): void
    {
        if (! $this->filesystem->fileExists($filename)) {
            throw new FileNotFoundException('Cannot locate configuration file '.$filename);
        }

        $this->loadConfig($this->filesystem->read($filename));
    }

    public function loadConfig(array|string $config): void
    {
        if (is_string($config)) {
            $config = json_decode(
                $config,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        $this->setServers($config);
    }

    protected function setServers(array $config): void
    {
        $commonConfig = Arr::get($config, 'common');

        $this->servers = collect(Arr::get($config, 'servers'))
            ->map(fn ($serverConfig, $name) => new Server($name, array_merge($serverConfig, $commonConfig)));
    }

    public function serverNames(): array
    {
        return $this->servers->keys()->toArray();
    }

    public function servers(): Collection
    {
        return $this->servers;
    }

    public function server(string $name): Server
    {
        if (! $this->servers->has($name)) {
            throw new ConfigurationException('Server with name '.$name.' not configured');
        }

        return $this->servers->get($name);
    }
}
