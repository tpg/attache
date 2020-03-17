<?php

namespace TPG\Attache\Tests;

use Carbon\Carbon;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Console\ReleasesListCommand;
use TPG\Attache\ReleaseService;

class ReleaseTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_get_a_list_of_releases()
    {
        $config = new ConfigurationProvider(__DIR__.'/attache-test.json');
        $server = $config->server('production');

        $service = \Mockery::mock(ReleaseService::class, [$server])->makePartial();
        $service->shouldReceive('fetch')
            ->once();

        $service->shouldReceive('list')
            ->once()
            ->andReturn([
                '20200101010100',
                '20200101010200',
            ]);

        $service->shouldReceive('active')
            ->once()
            ->andReturn('20200101010200');

        $application = new Application();
        $command = new ReleasesListCommand(null, $config, $service);
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['server' => 'production']);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString(
            Carbon::createFromFormat('YmdHis', '20200101010200')->format('d F Y H:i'),
            $output
        );
    }
}
