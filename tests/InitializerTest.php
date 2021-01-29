<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

use Illuminate\Support\Arr;
use TPG\Attache\Initializer;

class InitializerTest extends TestCase
{
    /**
     * @test
     **/
    public function it_can_create_a_new_config_file(): void
    {
        $initializer = new Initializer($this->filesystem);
        $initializer->create();

        self::assertTrue($this->filesystem->fileExists('.attache.json'));
        self::assertSame(
            $initializer->config('git@testremote.com:vendor/project.git'),
            json_decode($this->filesystem->read('.attache.json'), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @test
     **/
    public function it_will_discover_the_git_remote(): void
    {
        $initializer = new Initializer($this->filesystem);
        $initializer->create();

        $config = json_decode($this->filesystem->read('.attache.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('git@testremote.com:vendor/project.git', Arr::get($config, 'repository'));
    }
}
