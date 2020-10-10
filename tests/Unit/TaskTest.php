<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Unit;

use TPG\Attache\Result;
use TPG\Attache\Server;
use TPG\Attache\Targets\Local;
use TPG\Attache\Targets\Ssh;
use TPG\Attache\Task;
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
    public function it_can_return_a_bash_script(): void
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
    public function it_can_run_a_task_locally(): void
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
    public function it_can_connect_via_ssh_to_run_a_task(): void
    {
        $task = new Task('echo "Hello, World!"');

        $server = \Mockery::mock(Server::class)
            ->makePartial();

        $server->setConfig([
            'host' => 'example.com',
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

    /**
     * @test
     */
    public function it_can_track_percentage_progress_when_running_multiple_tasks(): void
    {
        $tasks = [
            new Task('ls'),
            new Task('ls'),
            new Task('ls'),
            new Task('ls'),
            new Task('ls'),
        ];

        $runner = new TaskRunner(new Local(__DIR__));

        $percentage = [];
        $runner->run($tasks, function (Result $result, int $progress) use (&$percentage) {
            $percentage[] = $progress;
        });

        $this->assertSame([
            20, 40, 60, 80, 100,
        ], $percentage);
    }
}
