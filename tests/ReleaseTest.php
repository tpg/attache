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
    public function it_can_process_release_data_from_the_server()
    {
        $config = $this->getConfig();

        $releaseService = \Mockery::mock(ReleaseService::class, [$config->server('server-1')])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $releaseService->shouldReceive('getReleases')
            ->once()
            ->andReturn(explode(PHP_EOL, $this->releaseListData()));

        $releaseService->shouldReceive('getActiveRelease')
            ->once()
            ->andReturn('20200101010300');

        $releaseService->fetch();

        $this->assertSame([
            '20200101010100',
            '20200101010200',
            '20200101010300',
        ], $releaseService->list());

        $this->assertSame('20200101010300', $releaseService->active());
    }

    /**
     * @test
     */
    public function it_can_check_if_a_release_exists()
    {
        $config = $this->getConfig();

        $releaseService = \Mockery::mock(ReleaseService::class, [$config->server('server-1')])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $releaseService->shouldReceive('getReleases')
            ->once()
            ->andReturn(explode(PHP_EOL, $this->releaseListData()));

        $releaseService->shouldReceive('getActiveRelease')
            ->once();

        $releaseService->fetch();

        $this->assertTrue($releaseService->exists('20200101010200'));
    }

    protected function releaseListData(): string
    {
        return '20200101010100'.PHP_EOL.'20200101010200'.PHP_EOL.'20200101010300';
    }

    protected function currentReleaseData(): string
    {
        return 'total 1932'.
            '-rwxr-xr-x  1 ubuntu ubuntu 1969526 Mar 17 13:13 composer.phar'.PHP_EOL.
            'lrwxrwxrwx  1 ubuntu ubuntu      44 Mar 18 20:56 live -> /app/releases/20200101010300'.PHP_EOL.
            'drwxrwxr-x  5 ubuntu ubuntu    4096 Mar 18 20:56 releases'.PHP_EOL.
            'drwxrwxr-x+ 5 ubuntu ubuntu    4096 Feb 15 06:33 storage';
    }

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

    /**
     * @test
     */
    public function it_can_prune_releases()
    {
        $config = new ConfigurationProvider(__DIR__ . '/attache-test.json');
        $server = $config->server('production');
        $server->setConfig(['prune' => 2]);

        $this->assertSame(2, $server->prune());
    }
}
