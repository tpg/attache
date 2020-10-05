<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Unit;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use TPG\Attache\Initializer;
use TPG\Attache\Server;

class ServerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_store_a_server_cnofig()
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
        $this->assertTrue($server->migrate());
    }

    /**
     * @test
     */
    public function it_can_get_the_configured_paths()
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $initializer = new Initializer($filesystem);

        $config = $initializer->defaultConfig('remote..com')['servers']['production'];

        $server = new Server('production', $config);
        $root = $server->root(true);

        $this->assertSame($root.'releases', $server->path('releases'));
        $this->assertSame($root.'live', $server->path('serve'));
        $this->assertSame($root.'storage', $server->path('storage'));
        $this->assertSame($root.'.env', $server->path('env'));
    }
}
