<?php

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TPG\Attache\Exceptions\ConfigurationException;

class Initializer
{
    /**
     * @var array
     */
    protected array $remotes = [];

    /**
     * @param  string|null  $gitConfigFilename
     *
     * @throws ConfigurationException
     */
    public function __construct(string $gitConfigFilename = null)
    {
        if (! $gitConfigFilename) {
            $gitConfigFilename = $this->getDefaultGitConfigFilename();
        }

        $this->loadGitConfig($gitConfigFilename);
    }

    protected function getDefaultGitConfigFilename(): string
    {
        return '.git/config';
    }

    /**
     * Load the Git config from a config file.
     *
     * @param  string  $filename
     *
     * @throws ConfigurationException
     */
    public function loadGitConfig(string $filename): void
    {
        if (! file_exists($filename)) {
            throw new ConfigurationException('Not a git repository');
        }
        $ini = file_get_contents($filename);
        $this->discoverGitRemotes($ini);
    }

    /**
     * Create a new configuration file.
     *
     * @param  string  $filename
     * @param  string  $gitUrl
     */
    public function createConfig(string $filename, string $gitUrl = null): void
    {
        $config = $this->getConfig($gitUrl);

        file_put_contents(
            $filename,
            json_encode(
                $config,
                JSON_THROW_ON_ERROR
                | JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES,
                512
            )
        );
    }

    /**
     * Discover the configured Git remote URL.
     *
     * @param  string  $gitConfig
     */
    public function discoverGitRemotes(string $gitConfig): void
    {
        $ini = parse_ini_string($gitConfig, true);
        $this->remotes = $this->getGitConfigKeys($ini);
    }

    /**
     * @param  array  $ini
     * @return array
     */
    protected function getGitConfigKeys(array $ini): array
    {
        $keys = array_values(array_filter(array_keys($ini), static function ($key) {
            return Str::startsWith($key, 'remote');
        }));

        $remotes = [];
        foreach ($keys as $key) {
            $remotes[Str::after($key, 'remote ')] = Arr::get($ini, $key.'.url');
        }

        return $remotes;
    }

    /**
     * Check if there are multiple Git remotes configured.
     *
     * @return bool
     */
    public function hasMultipleRemotes(): bool
    {
        return count($this->remotes) > 1;
    }

    /**
     * Get an array of Git remotes.
     *
     * @return array
     */
    public function remotes(): array
    {
        return $this->remotes;
    }

    /**
     * Get the named Git remote URL.
     *
     * @param  string  $name
     * @return string|null
     */
    public function remote(string $name = 'origin'): ?string
    {
        return Arr::get($this->remotes(), $name);
    }

    /**
     * Get the default configuration.
     *
     * @param  string  $remote
     * @return array
     */
    protected function getConfig(string $remote = null): array
    {
        return [
            'repository' => $remote ?: 'git@remote.com:vendor/repository.git',
            'servers' => [
                'production' => [
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
                ],
            ],
        ];
    }
}
