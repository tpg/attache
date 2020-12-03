<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use TPG\Attache\Contracts\ConfigurationProviderContract;
use TPG\Attache\Contracts\ServerContract;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Exceptions\FilesystemException;

class ConfigurationProvider implements ConfigurationProviderContract
{
    protected FilesystemInterface $filesystem;
    protected ?Collection $servers = null;
    protected string $repository;
    protected ?string $default;


    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function load(string $filename): void
    {
        try {
            $this->setConfig(
                json_decode(
                    $this->filesystem->read($filename),
                    true,
                    512,
                    JSON_THROW_ON_ERROR)
            );
        } catch (FileNotFoundException $e) {
            throw new FilesystemException('Config file '.$filename.' not found.');
        } catch (\JsonException $e) {
            throw new ConfigurationException('Unable to read configuration.'."\nJSON error: ".$e->getMessage());
        }
    }

    public function setConfig(array $config): void
    {
        $servers = Arr::get($config, 'servers');
        if (! $servers) {
            throw new ConfigurationException('No servers have been configured.');
        }

        $this->setServers(
            $servers,
            Arr::get($config, 'common', [])
        );

        $this->repository = Arr::get($config, 'repository');
        $this->default = Arr::get($config, 'default');

        (new ConfigurationValidator($this))->validate();
    }

    protected function setServers(array $servers, array $commonConfig = []): void
    {
        $this->servers = collect($servers)
            ->map(function ($serverConfig, $name) use ($commonConfig) {
                return new Server($name, array_replace_recursive($commonConfig, $serverConfig));
            })
            ->keyBy->name();
    }

    public function configured(): bool
    {
        return $this->servers && $this->servers->count() >= 1;
    }

    public function repository(): string
    {
        return $this->repository;
    }

    public function servers(): Collection
    {
        return $this->servers;
    }

    public function server(string $name): ServerContract
    {
        return $this->servers->get($name);
    }

    public function default(): ?string
    {
        return $this->default;
    }

    public function defaultServer(): ?ServerContract
    {
        $server = null;

        if ($this->servers->count() === 1) {
            $server = $this->servers->first();
        }

        if ($this->default) {
            $server = $this->servers->get($this->default);
        }

        return $server;
    }
}
