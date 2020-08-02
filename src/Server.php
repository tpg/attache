<?php

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Server
{
    protected string $name;

    protected array $config = [
        'branch' => 'master',
        'host' => null,
        'port' => 22,
        'user' => null,
        'root' => null,
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
        'assets' => [
            'public/js' => 'public/js',
            'public/css' => 'public/css',
            'public/mix-manifest.json' => 'public/mix-manifest.json',
        ],
        'scripts' => [],
    ];

    /**
     * @param string $name
     * @param array|null $config
     */
    public function __construct(string $name, array $config = null)
    {
        $this->setName($name);

        if ($config) {
            $this->setConfig($config);
        }
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the server config.
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = array_replace_recursive($this->config, $config);

        return $this;
    }

    /**
     * Get the server config value by key or the config array.
     *
     * @param null $key
     * @return mixed
     */
    public function config($key = null)
    {
        return Arr::get($this->config, $key);
    }

    /**
     * Get the name of the server.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get the host.
     *
     * @return string
     */
    public function host(): string
    {
        return Arr::get($this->config, 'host');
    }

    /**
     * Get the port number.
     *
     * @return int
     */
    public function port(): int
    {
        return (int) Arr::get($this->config, 'port', 22);
    }

    /**
     * Get the server user.
     *
     * @return string
     */
    public function user(): string
    {
        return Arr::get($this->config, 'user');
    }

    /**
     * Get the application root path.
     *
     * @param bool $trailingSlash
     * @return string
     */
    public function root(bool $trailingSlash = false): string
    {
        $root = Arr::get($this->config, 'root', '');

        if ($trailingSlash && ! Str::endsWith($root, '/')) {
            return $root.'/';
        }

        return Str::endsWith($root, '/')
            ? substr($root, 0, -1)
            : $root;
    }

    /**
     * Get the Git branch.
     *
     * @return string
     */
    public function branch(): string
    {
        return Arr::get($this->config, 'branch');
    }

    /**
     * Get an array of application paths.
     *
     * @return array
     */
    public function paths(): array
    {
        return Arr::get($this->config, 'paths');
    }

    /**
     * Get an application path by its key.
     *
     * @param string $key
     * @param bool $absolute
     * @return ?string
     */
    public function path(string $key, bool $absolute = true): ?string
    {
        $path = Arr::get($this->paths(), $key);

        if (! $path) {
            $path = $key;
        }

        return $absolute
            ? $this->root(true).$path
            : $path;
    }

    /**
     * Check if the migration command must be run.
     *
     * @return bool
     */
    public function migrate(): bool
    {
        return Arr::get($this->config, 'migrate', false);
    }

    /**
     * Get the name of the PHP binary.
     *
     * @return string
     */
    public function phpBin(): string
    {
        return Arr::get($this->config, 'php.bin', 'php');
    }

    /**
     * Get a composer configuration value.
     *
     * @param null $key
     * @return mixed
     */
    public function composer($key = null)
    {
        $key = 'composer'.($key ? '.'.$key : null);

        return Arr::get($this->config, $key);
    }

    /**
     * Get the file path of the composer binary.
     *
     * @return string
     */
    public function composerBin(): string
    {
        $bin = Arr::get($this->config, 'composer.bin');
        if (Arr::get($this->config, 'composer.local', false)) {
            $bin = $this->root().'/'.$bin;
        }

        return $bin;
    }

    /**
     * Get an asset target.
     *
     * @param string|null $key
     * @return mixed
     */
    public function assets(string $key = null)
    {
        $key = 'assets'.($key ? '.'.$key : null);

        return Arr::get($this->config, $key, null);
    }

    /**
     * Get a script hook value by its key.
     *
     * @param string $key
     * @param string|null $releaseId
     * @param string|null $path
     * @return array
     */
    public function script(string $key, string $releaseId = null, string $path = null): array
    {
        $script = Arr::get($this->config, 'scripts.'.$key, []);

        return (new ScriptCompiler($this))->setReleaseId($releaseId)->compile($script, $path);
    }

    /**
     * Get the most recent release ID.
     *
     * @return string
     * @deprecated 0.6.2 No longer used to fetch the current release ID.
     */
    public function latestReleaseId(): string
    {
        return array_reverse($this->releaseIds())[0];
    }

    /**
     * Get an array of release IDs.
     *
     * @return array
     */
    public function releaseIds(): array
    {
        $paths = glob($this->path('releases'), GLOB_ONLYDIR);

        $releases = array_map(fn ($path) => basename($path), $paths);
        sort($releases);

        return $releases;
    }

    /**
     * Get the SSH connection string for the server.
     *
     * @return string
     */
    public function sshConnectionString(): string
    {
        return $this->user().'@'.$this->host().' -p'.$this->port();
    }
}
