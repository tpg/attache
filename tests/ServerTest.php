<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;

class ServerTest extends TestCase
{
    protected Initializer $initializer;
    protected ConfigurationProvider $configurationProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializer = new Initializer($this->filesystem);
        $this->initializer->create();
        $this->configurationProvider = new ConfigurationProvider($this->filesystem, '.attache.json');
    }

    /**
     * @test
     **/
    public function it_can_return_an_ssh_connection_string(): void
    {
        $server = $this->configurationProvider->server('production');

        self::assertSame('user@example.test -p22', $server->connectionString());
    }

    /**
     * @test
     **/
    public function it_will_return_the_php_bin(): void
    {
        self::assertSame('php', $this->configurationProvider->server('production')->phpBin());
    }

    /**
     * @test
     **/
    public function it_will_return_the_composer_binary_location(): void
    {
        $server = $this->configurationProvider->server('production');

        self::assertSame($server->rootPath(true).'composer.phar', $server->composerBin());
    }

    /**
     * @test
     **/
    public function it_will_return_a_path_by_name(): void
    {
        $server = $this->configurationProvider->server('production');

        self::assertSame($server->rootPath(true).'releases', $server->path('releases', true));
    }
}
