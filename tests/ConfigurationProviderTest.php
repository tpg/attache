<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

use Illuminate\Support\Arr;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;

class ConfigurationProviderTest extends TestCase
{
    protected Initializer $initializer;
    protected array $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initializer = new Initializer($this->filesystem);
        $this->initializer->create();
    }

    /**
     * @test
     **/
    public function it_can_load_configured_servers(): void
    {
        $provider = new ConfigurationProvider($this->filesystem, '.attache.json');
        $server = $provider->servers()->first();

        self::assertSame('production', $server->name());
        self::assertSame('user@example.test -p22', $server->connectionString());
        self::assertSame(['production'], $provider->serverNames());
    }

    /**
     * @test
     **/
    public function it_will_merge_common_config_into_server_configs(): void
    {
        $provider = new ConfigurationProvider($this->filesystem, '.attache.json');
        $server = $provider->server('production');

        self::assertSame('-b master --depth=1 "git@testremote.com:vendor/project.git"', $server->cloneString());
    }

    /**
     * @test
     **/
    public function it_can_return_a_single_server_as_default(): void
    {
        $provider = new ConfigurationProvider($this->filesystem, '.attache.json');
        $server = $provider->defaultServer();

        self::assertSame('production', $server->name());
    }

    /**
     * @test
     **/
    public function it_will_return_a_default_server_if_one_is_configured(): void
    {
        $provider = new ConfigurationProvider($this->filesystem);

        $config = json_decode($this->filesystem->read('.attache.json'), true, 512, JSON_THROW_ON_ERROR);

        Arr::set($config, 'servers.staging', [
            'host' => 'another.test',
            'username' => 'someone',
            'port' => 22,
            'root' => '/path/to/test',
        ]);

        Arr::set($config, 'default', 'production');

        $provider->loadConfig($config);

        $server = $provider->defaultServer();

        self::assertSame('production', $server->name());
    }
}
