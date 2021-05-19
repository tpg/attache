<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

use Laravel\Envoy\Task;
use Mockery;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\Commands\ReleasesListCommand;
use TPG\Attache\Contracts\ReleaseManagerInterface;
use TPG\Attache\Initializer;
use TPG\Attache\ReleaseManager;
use TPG\Attache\Server;

class ReleasesListCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $initializer = new Initializer($this->filesystem);
        $initializer->create();
    }

    /**
     * @test
     **/
    public function it_can_display_a_list_of_releases(): void
    {
        $mock = Mockery::mock(
            ReleaseManager::class,
            ReleaseManagerInterface::class,
            [new Server('Test Server', $this->testServerConfig())]);
        $mock->makePartial();
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getTask')
            ->andReturn(
                new Task(
                    ['127.0.0.1'], '',
                    'echo "ATTACHE-SCRIPT"'."\n"
                    .'echo "20210101010101"'."\n"
                    .'echo "ATTACHE-DELIM"'."\n"
                    .'echo "live -> /project/root/releases/20210101010101"'."\n"
                    .'echo "ATTACHE-SCRIPT"'
                )
            );

        $command = new ReleasesListCommand($this->filesystem);
        $command->setReleaseManager($mock);
        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertStringContainsString('* 20210101010101', $commandTester->getDisplay());
        self::assertStringContainsString('01 January 2021 01:01:01', $commandTester->getDisplay());
    }

    protected function testServerConfig(): array
    {
        return [
            'root' => '/',
            'username' => 'test',
            'host' => '127.0.0.1',
            'port' => 22,
        ];
    }
}
