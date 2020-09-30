<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Unit;

use Symfony\Component\Console\Output\BufferedOutput;
use TPG\Attache\Initializer;
use TPG\Attache\Printer;

class InitializerTest extends TestCase
{
    /**
     * @var Initializer
     */
    protected Initializer $initializer;

    protected function setUp(): void
    {
        parent::setUp();
        $output = new BufferedOutput();
        $printer = new Printer($output);
        $this->initializer = new Initializer($printer);
    }

    /**
     * @test
     */
    public function it_can_discover_a_git_remote()
    {
        $this->initializer->loadGitConfig();

        $this->assertSame('git@remote.test:/vendor/repository.git', $this->initializer->remote());
    }
}
