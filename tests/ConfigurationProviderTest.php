<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

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

        self::assertSame('-b master --depth=1 git@testremote.com:vendor/project.git', $server->cloneString());
    }
}
