<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Feature;

use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use TPG\Attache\Commands\InitCommand;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;
use TPG\Attache\Printer;

class InitCommandTest extends TestCase
{
    /*
     * attache init
     *
     * Creates a new `.attache.json` in the current directory.
     * Will attempt to find the current GIT remote URL.
     * Can take a `--config` parameter to name the config file.
     */

    /**
     * @test
     */
    public function it_can_generate_a_basic_config_file()
    {
        $provider = new ConfigurationProvider();
        $output = new BufferedOutput();
        $initializer = new Initializer(new Printer($output));

        $command = new InitCommand(null, $provider, $initializer);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--filename' => __DIR__.'/../.attache.json',
        ]);

        self::assertFileExists(__DIR__.'/../.attache.json');

        unlink(__DIR__.'/../.attache.json');
    }
}
