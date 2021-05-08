<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

use TPG\Attache\Initializer;

class InitializerTest extends TestCase
{
    protected Initializer $initializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializer = new Initializer($this->filesystem);
    }

    /**
     * @test
     **/
    public function it_can_discover_a_git_remote(): void
    {
        $remote = $this->initializer->discoverGitRemotes();

        self::assertArrayHasKey('origin', $remote);
        self::assertSame('git@testremote.com:vendor/project.git', $remote['origin']);
    }

    /**
     * @test
     **/
    public function it_can_discover_multiple_git_remotes(): void
    {
        $config = explode("\n", $this->filesystem->read('.git/config'));
        $config = array_merge($config, [
            '[remote "upstream"]',
            "\turl = git@testremote.com:vendor/project2.git",
            "\tfetch = +refs/heads/*:refs/remotes/origin/*",
        ]);

        $this->filesystem->write('.git/config', implode("\n", $config));

        $remotes = $this->initializer->discoverGitRemotes();

        self::assertSame([
            'origin' => 'git@testremote.com:vendor/project.git',
            'upstream' => 'git@testremote.com:vendor/project2.git',
        ], $remotes);
    }

    /**
     * @test
     **/
    public function it_can_create_a_new_config_file(): void
    {
        $this->initializer->create();
        self::assertTrue($this->filesystem->fileExists('.attache.json'));
    }
}
