<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Exceptions\FilesystemException;

class ConfigurationProvider
{
    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;
    protected Collection $servers;

    /**
     * ConfigurationProvider constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
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

    protected function setConfig(array $config): void
    {
        $this->repository = Arr::get($config, 'repository');
        $this->default = Arr::get($config, 'default');

        $servers = Arr::get($config, 'servers');
        if (! $servers) {
            throw new ConfigurationException('No servers have been configured.');
        }

        $this->setServers(
            $servers,
            Arr::get($config, 'common', [])
        );
    }

    protected function setServers(array $servers, array $commonConfig = []): void
    {
        $this->servers = collect($servers)->each(function ($serverConfig) use ($commonConfig) {
            return new Server(array_replace_recursive($commonConfig, $serverConfig));
        });
    }
}
