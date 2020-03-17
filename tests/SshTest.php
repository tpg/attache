<?php

namespace TPG\Attache\Tests;

use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Ssh;
use TPG\Attache\Task;

class SshTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_run_a_task_against_a_server()
    {
        $config = new ConfigurationProvider(__DIR__.'/attache-test.json');
        $server = $config->server('production');

        $task = \Mockery::mock(Task::class, ['echo "testing"']);
        $task->shouldReceive('script')
            ->once()
            ->andReturn('echo "testing"');

        $ssh = new Ssh($task);

        $this->assertStringContainsString('echo "testing"', $ssh->script());
    }
}
