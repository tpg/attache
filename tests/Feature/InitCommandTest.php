<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Feature;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\Commands\InitCommand;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;

class InitCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_generate_a_config_file(): void
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $configurationProvider = new ConfigurationProvider($filesystem);
        $initializer = new Initializer($filesystem);

        $this->createGitConfig($filesystem);

        $command = new InitCommand(null, $configurationProvider, $initializer);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $config = json_decode($filesystem->read('.attache.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($initializer->defaultConfig('git-remote-1.com'), $config);

        $filesystem->deleteDir('.git');
        $filesystem->delete('.attache.json');
    }

    /**
     * @test
     */
    public function it_will_allow_you_to_select_a_remote()
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $configurationProvider = new ConfigurationProvider($filesystem);
        $initializer = new Initializer($filesystem);

        $this->createGitConfig($filesystem, [
            'remote1' => 'git-remote-1.com',
            'remote2' => 'git-remote-2.com',
        ]);

        $application = new Application();
        $command = new InitCommand(null, $configurationProvider, $initializer);
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['remote2']);
        $commandTester->execute([]);

        $this->assertSame(
            $initializer->defaultConfig('git-remote-2.com'),
            json_decode($filesystem->read('.attache.json'), true, 512, JSON_THROW_ON_ERROR)
        );

        $filesystem->delete('.attache.json');
    }

    protected function getFilesystem(): Filesystem
    {
        return new Filesystem(new Local(__DIR__));
    }

    protected function createGitConfig(Filesystem $filesystem, array $remotes = []): void
    {
        $remotes = $remotes ?: ['origin' => 'git-remote-1.com'];

        $config = collect($remotes)->map(function ($remote, $key) {
            return implode("\n", [
                '[remote "'.$key.'"]',
                "\turl = ".$remote,
            ]);
        })->values();

        $filesystem->put('.git/config', implode("\n", $config->toArray()));
    }
}
