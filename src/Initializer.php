<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use TPG\Attache\Exceptions\FilesystemException;

class Initializer
{
    protected array $gitRemotes;
    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * Initializer constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function create(string $filename, string $gitRemote): void
    {
        $config = $this->defaultConfig($gitRemote);

        $this->filesystem->put($filename, json_encode(
            $config,
            JSON_PRETTY_PRINT
            | JSON_THROW_ON_ERROR
            | JSON_UNESCAPED_SLASHES
        ));
    }

    public function discoverGitRemotes(): Collection
    {
        try {
            $gitConfig = $this->filesystem->read('.git/config');

            $ini = parse_ini_string($gitConfig, true);

            $sections = collect(array_keys($ini))->filter(function ($section) {
                return Str::startsWith($section, 'remote');
            });

            return $sections->mapWithKeys(function ($section) use ($ini) {
                return [Str::after($section, 'remote ') => $ini[$section]['url']];
            });
        } catch (FileNotFoundException $e) {
            throw new FilesystemException('Not a Git repository.');
        }
    }

    public function defaultConfig(string $gitRemote): array
    {
        return [
            'repository' => $gitRemote,
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
            'user' => 'user',
            'root' => '/path/to/application',
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
            ],
            'branch' => 'master',
            'migrate' => false,
        ];
    }
}
