<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Feature;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class InitCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_generate_a_basic_config_file(): void
    {
    }

    protected function getFilesystem(): Filesystem
    {
        return new Filesystem(new Local(__DIR__));
    }
}
