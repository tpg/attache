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
     * @throws ConfigurationException
     */
    public function __construct()
    {
        $this->discoverGitRemotes();
    }

    /**
     * Create a new configuration file.
     *
     * @param string $filename
     * @param string $gitUrl
     */
    public function createConfig(string $filename, string $gitUrl): void
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
     * @throws ConfigurationException
     */
    protected function discoverGitRemotes(): void
    {
        if (! file_exists('.git/config')) {
            throw new ConfigurationException('Not a git repository');
        }

        $ini = parse_ini_file('.git/config', true);

        $keys = array_values(array_filter(array_keys($ini), static function ($key) {
            return Str::startsWith($key, 'remote');
        }));

        foreach ($keys as $key) {
            $this->remotes[Str::after($key, 'remote ')] = Arr::get($ini, $key.'.url');
        }
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
     * @param string $name
     * @return string|null
     */
    public function remote(string $name = 'origin'): ?string
    {
        return Arr::get($this->remotes(), $name);
    }

    /**
     * Get the default configuration.
     *
     * @param string $remote
     * @return array
     */
    protected function getConfig(string $remote): array
    {
        return [
            'repository' => $remote,
            'servers' => [
                [
                    'name' => 'production',
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
