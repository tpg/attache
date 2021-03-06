<?php

namespace TPG\Attache\Tests;

use Illuminate\Support\Arr;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;

class InitializerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents('gitconfig', implode("\n", [
            '[remote "origin"]',
            'url = git@origin.git',
            '[remote "upstream"]',
            'url = git@upstream.git',
        ]));
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        unlink('gitconfig');
    }

    /**
     * @test
     */
    public function it_can_discover_the_git_remotes()
    {
        $initializer = new Initializer('gitconfig');

        $this->assertTrue($initializer->hasMultipleRemotes());
        $this->assertArrayHasKey('origin', $initializer->remotes());
        $this->assertArrayHasKey('upstream', $initializer->remotes());
    }

    /**
     * @test
     */
    public function it_can_create_a_base_config_with_a_git_remote()
    {
        $initializer = new Initializer('gitconfig');

        $initializer->createConfig(__DIR__.'/test-config.json', $initializer->remote());

        $config = json_decode(
            file_get_contents(__DIR__.'/test-config.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame('git@origin.git', $config['repository']);
        $this->assertSame('example.test', Arr::get($config, 'servers.production.host'));

        $provider = new ConfigurationProvider();
        $provider->setConfig($config);

        $this->assertSame('example.test', $provider->server('server-1')->host());

        unlink(__DIR__.'/test-config.json');
    }
}
