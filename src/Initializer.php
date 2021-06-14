<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonException;
use League\Flysystem\Filesystem;
use TPG\Attache\Contracts\InitializerInterface;

class Initializer implements InitializerInterface
{
    protected Filesystem $filesystem;

    /**
     * Initializer constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function create(array $config = null, string $filename = '.attache.json'): void
    {
        $config = $config
            ?? $this->config(
                Arr::first($this->discoverGitRemotes())
            );

        try {
            $this->filesystem->write(
                $filename,
                json_encode($config, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        } catch (JsonException) {
        }
    }

    public function discoverGitRemotes(): array
    {
        $gitConfig = $this->filesystem->read('.git/config');

        $ini = parse_ini_string($gitConfig, process_sections: true);

        $remoteKeys = collect(array_keys($ini))
            ->filter(fn ($key) => Str::startsWith($key, 'remote'))->values();

        return $remoteKeys->mapWithKeys(
            fn ($key) => [Str::after($key, 'remote ') => Arr::get($ini, $key.'.url')]
        )->toArray();
    }

    public function config(string $remote): array
    {
        return [
            'common' => [
                'git' => [
                    'repository' => $remote,
                    'depth' => 1,
                    'branch' => 'master',
                ],
            ],
            'servers' => [
                'production' => $this->defaultServerConfig(),
            ],
        ];
    }

    protected function defaultServerConfig(): array
    {
        return [
            'host' => 'example.test',
            'port' => 22,
            'username' => 'user',
            'root' => '/path/to/application',
            'git' => [
                'branch' => 'develop',
            ],
            'paths' => [
                'releases' => 'releases',
                'serve' => 'live',
                'storage' => 'storage',
                'env' => '.env',
            ],
            'php' => [
                'bin' => 'php',
            ],
            'steps' => [
                'build' => [
                    'enabled' => true,
                    'before' => [
                        'echo "Starting build..."',
                    ],
                    'after' => [],
                ],
                'install' => [
                    'enabled' => true,
                    'before' => [
                        'echo "Installing application..."',
                    ],
                    'after' => [],
                ],
                'dependencies' => [
                    'enabled' => true,
                    'before' => [
                        'echo "Installing dependencies..."',
                    ],
                    'after' => [],
                ],
                'assets' => [
                    'enabled' => true,
                    'before' => [
                        'echo "Copying compiled assets..."',
                    ],
                    'after' => [],
                ],
                'live' => [
                    'enabled' => true,
                    'before' => [
                        'echo "Going live..."',
                    ],
                    'after' => [
                        '@artisan cache:clear',
                    ]
                ],
            ],
            'branch' => 'master',
            'migrate' => false,
        ];
    }
}
