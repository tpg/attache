<?php

namespace TPG\Attache\Tests;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Console\DeployCommand;
use TPG\Attache\Deployer;

class DeployerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_deploy_to_a_specified_server()
    {
        $config = new ConfigurationProvider(__DIR__.'/attache-test.json');
        $server = $config->server('production');

        $input = new ArrayInput(['server' => 'production']);
        $output = new ConsoleOutput();

        $id = date('YmdHis');

        $deployer = \Mockery::mock(Deployer::class, [$config, $server, $input, $output]);
        $deployer->shouldReceive('deploy')
            ->once()
            ->with($id);

        $command = new DeployCommand(null, $config, $deployer);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['server' => 'production']);
        $this->assertStringContainsString($id, $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_can_insert_script_hooks()
    {
        $config = new ConfigurationProvider(__DIR__.'/attache-test.json');
        $server = $config->server('production');

        $script = implode(PHP_EOL, array_merge($server->script('before-build'), ['yarn prod']));

        $input = new ArrayInput(['server' => 'production']);
        $output = new ConsoleOutput();

        $id = date('YmdHis');

        $deployer = new Deployer($config, $server, $input, $output);
        $tasks = $deployer->getTasks($server, $id);

        $this->assertSame($script, $tasks[0]->script());
    }
}
