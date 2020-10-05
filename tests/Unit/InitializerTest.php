<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Unit;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use TPG\Attache\Exceptions\FilesystemException;
use TPG\Attache\Initializer;

class InitializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_discover_git_remotes(): void
    {
        $filesystem = $this->getFilesystem();
        $initializer = new Initializer($filesystem);

        $filesystem->put('.git/config', implode("\n", [
            '[remote "origin"]',
            "\turl = git-remote-1.com",
            '[remote "upstream"]',
            "\turl = git-remote-2.com",
        ]));

        $remotes = $initializer->discoverGitRemotes();

        $this->assertSame([
            'origin' => 'git-remote-1.com',
            'upstream' => 'git-remote-2.com',
        ], $remotes->toArray());

        $filesystem->deleteDir('.git');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_not_a_git_repository(): void
    {
        $filesystem = $this->getFilesystem();
        $initializer = new Initializer($filesystem);

        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage('Not a Git repository.');
        $initializer->discoverGitRemotes();
    }

    protected function getFilesystem(): Filesystem
    {
        return new Filesystem(new Local(__DIR__));
    }
}
