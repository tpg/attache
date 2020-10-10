<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Feature;

use League\Flysystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\Commands\ReleaseListCommand;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;
use TPG\Attache\ReleaseManager;
use TPG\Attache\Result;
use TPG\Attache\Targets\Ssh;
use TPG\Attache\Task;

class ReleaseListCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_get_a_list_of_releases(): void
    {
        $filesystem = $this->getFilesystem();
        $configurationProvider = $this->getConfigurationProvider($filesystem);

        $command = new ReleaseListCommand();
        $command->setConfigurationProvider($configurationProvider);

        $releaseManager = \Mockery::mock(ReleaseManager::class, [$configurationProvider->defaultServer()]);
        $releaseManager->shouldReceive('list')->once()
            ->andReturn(collect(['20200101010101', '20200101010102']));
        $releaseManager->shouldReceive('active')->once()
            ->andReturn('20200101010102');

        $command->setReleaseManager($releaseManager);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertStringContainsString('01 January 2020 01:01', $commandTester->getDisplay());
        $this->assertStringContainsString('<-- Active', $commandTester->getDisplay());
    }

    protected function getConfigurationProvider(Filesystem $filesystem): ConfigurationProvider
    {
        $provider = new ConfigurationProvider($filesystem);
        $initializer = new Initializer($filesystem);

        $config = $initializer->defaultConfig('remote.com');
        $config['servers']['production'] = [
            'host' => 'thepublicgood.dev',
            'port' => 5252,
            'user' => 'ubuntu',
            'root' => '/opt/tpg/battlefront',
        ];

        $provider->setConfig($config);

        return $provider;
    }
}
