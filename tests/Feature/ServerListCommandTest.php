<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Feature;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\Commands\ServerListCommand;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;

class ServerListCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_print_a_list_configured_servers(): void
    {
        $filesystem = $this->getFilesystem();
        $configurationProvider = new ConfigurationProvider($filesystem);

        $initializer = new Initializer($filesystem);
        $initializer->create('.attache.json', 'git-remote.com');

        $command = (new ServerListCommand())
            ->setConfigurationProvider($configurationProvider);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertStringContainsString('production    example.test', $commandTester->getDisplay());

        $filesystem->delete('.attache.json');
    }
}
