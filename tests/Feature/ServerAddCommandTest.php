<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Feature;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\Commands\ServerAddCommand;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;

class ServerAddCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_add_a_server_to_the_configuration()
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $configurationProvider = new ConfigurationProvider($filesystem);
        $initializer = new Initializer($filesystem);

        $initializer->create('.attache.json', 'remote.com');

        $command = new ServerAddCommand();
        $command->setFilesystem($filesystem);
        $command->setConfigurationProvider($configurationProvider);
        $command->setInitializer($initializer);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            'hostname.com',
            56,
            'user',
            '/root',
            'branch',
        ]);

        $commandTester->execute(['name' => 'testing']);

        $configurationProvider->load('.attache.json');

        $this->assertSame('hostname.com', $configurationProvider->server('testing')->host());

        $filesystem->delete('.attache.json');
    }
}
