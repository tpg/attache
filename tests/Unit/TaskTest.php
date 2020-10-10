<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Unit;

use Mockery\Mock;
use TPG\Attache\Server;
use TPG\Attache\Targets\Local;
use TPG\Attache\Targets\Ssh;
use TPG\Attache\Task;
use TPG\Attache\Result;
use TPG\Attache\TaskRunner;

class TaskTest extends TestCase
{
    /*
     * A Task represents any script or set of scripts that needs to be run.
     * Doesn't execute the task itself.
     * Can represent multiple scripts in a single task.
     * Can return a formatted Bash string.
     */

    /**
     * @test
     */
    public function it_can_return_a_bash_script()
    {
        $task = new Task('echo "Hello, World!"');

        $expected = implode(PHP_EOL, [
            'bash -se << \\'.$task->getBashDelimiter(),
            '(',
            'set -e',
            'echo "Hello, World!"',
            ')',
            $task->getBashDelimiter(),
        ]);

        $this->assertSame($expected, $task->bashScript());
    }

    /**
     * @test
     */
    public function it_can_run_a_task_locally()
    {
        $task = new Task('echo "Hello, World!"');

        $runner = new TaskRunner(new Local(__DIR__));
        $runner->run([$task], function (Result $result) use ($task) {
            $this->assertSame("Hello, World!\n", $result->output());
            $this->assertTrue($result->isOutput());
            $this->assertFalse($result->isError());
            $this->assertSame($task, $result->task());
        });
    }

    /**
     * @test
     */
    public function it_can_connect_via_ssh_to_run_a_task()
    {
        $task = new Task('echo "Hello, World!"');

        $server = \Mockery::mock(Server::class)
            ->makePartial();

        $server->setConfig([
            'host' => 'thepublicgood.dev',
            'user' => 'user',
            'root' => '/test/root',
        ]);

        $ssh = \Mockery::mock(Ssh::class, [$server]);
        $ssh->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('sshConnectionString')
            ->once()
            ->andReturn('');

        $runner = new TaskRunner($ssh);
        $runner->run([$task], function (Result $result) {
            $this->assertSame("Hello, World!\n", $result->output());
        });
    }
}
