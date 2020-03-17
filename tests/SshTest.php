<?php

namespace TPG\Attache\Tests;

use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Console\SshCommand;
use TPG\Attache\Ssh;
use TPG\Attache\Task;

class SshTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_run_a_task_against_a_server()
    {
        $config = $this->getConfig();

        $task = \Mockery::mock(Task::class, ['echo "testing"']);
        $task->shouldReceive('script')
            ->once()
            ->andReturn('echo "testing"');

        $ssh = new Ssh($task);

        $this->assertStringContainsString('echo "testing"', $ssh->script());
    }
}
