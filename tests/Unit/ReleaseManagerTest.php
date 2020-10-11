<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Unit;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Process\Process;
use TPG\Attache\Initializer;
use TPG\Attache\ReleaseManager;
use TPG\Attache\Result;
use TPG\Attache\Server;
use TPG\Attache\Task;
use TPG\Attache\TaskRunner;

class ReleaseManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_get_a_list_of_releases(): void
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $initializer = new Initializer($filesystem);

        $runner = \Mockery::mock(TaskRunner::class);
        $runner->shouldReceive('run')
            ->once()
            ->andReturn([0]);
        $runner->shouldReceive('hasError')
            ->andReturn(false);
        $runner->shouldReceive('getResults')
            ->once()
            ->andReturn(
                collect([
                    new Result(
                        new Task('ls'),
                        Process::OUT,
                        "20200101010101\n20200101010102"
                    ),
                ])
            );

        $manager = new ReleaseManager(new Server('production', $initializer->defaultServerConfig()));
        $manager->setTaskRunner($runner);

        $this->assertSame([
            '20200101010101',
            '20200101010102',
        ], $manager->list()->toArray());
    }

    /**
     * @test
     */
    public function it_can_get_the_active_release()
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        $initializer = new Initializer($filesystem);

        $runner = \Mockery::mock(TaskRunner::class);
        $runner->shouldReceive('run')
            ->once()
            ->andReturn([0]);
        $runner->shouldReceive('hasError')
            ->andReturn(false);
        $runner->shouldReceive('getResults')
            ->once()
            ->andReturn(
                collect([
                    new Result(
                        new Task('ls'),
                        Process::OUT,
                        'live -> /path/releases/20200101010102'
                    ),
                ])
            );

        $manager = new ReleaseManager(new Server('production', $initializer->defaultServerConfig()));
        $manager->setTaskRunner($runner);

        $this->assertSame('20200101010102', $manager->active());
    }
}
