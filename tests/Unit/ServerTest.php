<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Unit;

use Illuminate\Support\Arr;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use TPG\Attache\Initializer;
use TPG\Attache\Server;

class ServerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_store_a_server_config(): void
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $initializer = new Initializer($filesystem);

        $config = $initializer->defaultConfig('remote.com')['servers']['production'];

        $server = new Server('production', $config);
        $this->assertSame(22, $server->port());
        $this->assertSame('example.test', $server->host());
        $this->assertSame('user', $server->user());
        $this->assertSame('/path/to/application', $server->root());
        $this->assertSame('/path/to/application/', $server->root(true));
        $this->assertSame('master', $server->branch());
        $this->assertFalse($server->migrate());
    }

    /**
     * @test
     */
    public function it_can_get_the_configured_paths(): void
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $initializer = new Initializer($filesystem);

        $config = $initializer->defaultConfig('remote.com')['servers']['production'];

        $server = new Server('production', $config);
        $root = $server->root(true);

        $this->assertSame($root.'releases', $server->path('releases'));
        $this->assertSame($root.'live', $server->path('serve'));
        $this->assertSame($root.'storage', $server->path('storage'));
        $this->assertSame($root.'.env', $server->path('env'));
    }

    /**
     * @test
     */
    public function it_can_have_a_set_php_bin_config(): void
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $initializer = new Initializer($filesystem);

        $config = Arr::get($initializer->defaultConfig('remote.com'), 'servers.production');
        Arr::set($config, 'php.bin', '/path/to/php/bin');

        $server = new Server('production', $config);
        $this->assertSame('/path/to/php/bin', $server->php());
    }

    /**
     * @test
     */
    public function it_can_have_a_local_composer_path(): void
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $initializer = new Initializer($filesystem);

        $config = Arr::get($initializer->defaultConfig('remote.com'), 'servers.production');
        Arr::set($config, 'composer', [
            'bin' => 'composer-bin.phar',
            'local' => true,
        ]);

        $server = new Server('production', $config);
        $this->assertSame('/path/to/application/composer-bin.phar', $server->composer());
    }

    /**
     * @test
     */
    public function it_can_return_the_ssh_connection_string(): void
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $initializer = new Initializer($filesystem);

        $config = Arr::get($initializer->defaultConfig('remote.com'), 'servers.production');
        $server = new Server('production', $config);

        $this->assertSame('user@example.test -p22', $server->connectionString());
    }

    /**
     * @test
     */
    public function it_has_a_default_set_of_assets(): void
    {
        $server = new Server('production', ['host' => 'example.test']);

        $this->assertSame([
            'public/js' => 'public/js',
            'public/css' => 'public/css',
            'public/mix-manifest.json' => 'public/mix-manifest.json',
        ], $server->assets());
    }
}
