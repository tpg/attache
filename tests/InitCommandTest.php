<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\Commands\InitCommand;

class InitCommandTest extends TestCase
{
    /**
     * @test
     **/
    public function it_can_create_a_new_config_file(): void
    {
        $command = new InitCommand($this->filesystem);

        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--config' => 'another.json',
        ]);

        self::assertTrue($this->filesystem->fileExists('another.json'));
        $this->filesystem->delete('another.json');
    }

    /**
     * @test
     **/
    public function it_will_offer_a_choice_when_there_is_more_than_one_remote(): void
    {
        $config = array_merge($this->gitConfig(), [
            '[remote "upstream"]',
            "\t".'url = git@testremote.com:vendor/upstream.git',
            "\t".'fetch = +refs/heads/*:refs/remotes/origin/*',
        ]);

        $this->createGitConfigFile(implode("\n", $config));

        $command = new InitCommand($this->filesystem);
        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['origin']);
        $commandTester->execute([]);

        self::assertStringContainsString('origin', $commandTester->getDisplay());
        self::assertStringContainsString('upstream', $commandTester->getDisplay());

        $commandTester->execute([]);
    }

    /**
     * @test
     **/
    public function it_will_offer_to_upgrade_if_a_version_1_script_exists(): void
    {
        $this->filesystem->write('.attache.json', json_encode([
            'repository' => 'git@repo.test',
            'servers' => [
                'production' => [
                    'host' => 'server.test',
                    'port' => 22,
                    'user' => 'user',
                    'root' => '/root/to/application',
                    'paths' => [
                        'serve' => 'serve',
                    ],
                    'php' => [
                        'bin' => 'php80',
                    ],
                    'composer' => [
                        'bin' => 'composer',
                        'local' => true,
                    ],
                    'scripts' => [
                        'before-buid' => [
                            'php ./artisan cache:clear',
                        ],
                    ],
                    'branch' => 'develop',
                    'migrate' => false,
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $command = new InitCommand($this->filesystem);
        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        self::assertStringContainsString('Would you like to upgrade it?', $commandTester->getDisplay());

    }
}
