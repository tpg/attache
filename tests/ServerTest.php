<?php

namespace TPG\Attache\Tests;

use Symfony\Component\Console\Tester\CommandTester;
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
}
