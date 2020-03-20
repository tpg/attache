<?php

namespace TPG\Attache\Tests;

use http\Exception\RuntimeException;
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

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_no_server_is_set()
    {
        $task = new Task('echo "testing"');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No server to connect to');

        (new Ssh($task))->run();
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_no_task_is_set()
    {
        $ssh = new Ssh();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No task specified');

        $ssh->run();
    }
}
