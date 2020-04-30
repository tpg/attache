<?php

namespace TPG\Attache\Tests;

use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Console\ServersListCommand;

class ServerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_merge_a_common_configuration()
    {
        $config = $this->getConfig();

        $server = $config->server('server-2');

        $this->assertStringContainsString('server-2', $server->name());
        $this->assertStringContainsString('common-host', $server->host());
    }

    /**
     * @test
     */
    public function it_can_display_a_list_of_configured_servers()
    {
        $config = $this->getConfig();

        $command = new ServersListCommand(null, $config);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $this->assertStringContainsString('server-1', $commandTester->getDisplay());
        $this->assertStringContainsString('production.test', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_can_have_a_default_server_set()
    {
        $config = $this->getConfig();
        $server = $config->server();

        $this->assertSame('server-1', $server->name());
    }

    /**
     * @test
     */
    public function single_server_config_will_be_the_default_server()
    {
        $config = $this->config;
        $config['default'] = null;
        $config['servers'] = [
            [
                'name' => 'single',
                'host' => 'single-host',
                'user' => 'single-user',
                'port' => 22,
                'branch' => 'master',
                'root' => 'single-root',
            ],
        ];

        $provider = new ConfigurationProvider();
        $provider->setConfig($config);

        $server = $provider->server();

        $this->assertSame('single', $server->name());
    }

    /**
     * @test
     */
    public function it_can_have_a_custom_set_of_assets()
    {
        $config = $this->config;
        $config['servers'][0]['assets']['public/example'] = 'public/example';

        $provider = new ConfigurationProvider();
        $provider->setConfig($config);

        $server = $provider->server();

        $this->assertSame([
            'public/js' => 'public/js',
            'public/css' => 'public/css',
            'public/mix-manifest.json' => 'public/mix-manifest.json',
            'public/example' => 'public/example'
        ], $server->assets());
    }
}
