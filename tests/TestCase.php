<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->initFilesystem();
        $this->createGitConfigFile();
    }

    protected function tearDown(): void
    {
        $this->filesystem->deleteDirectory('.');
    }

    protected function initFilesystem(): void
    {
        $this->filesystem = new Filesystem(
            new LocalFilesystemAdapter(__DIR__.'/project')
        );
    }

    protected function createGitConfigFile(string $config = null): void
    {
        $this->filesystem->write('.git/config', $config ?? implode("\n", $this->gitConfig()));
    }

    protected function gitConfig(): array
    {
        return [
            '[core]',
            "\t".'repositoryformatversion = 0',
            "\t".'filemode = true',
            "\t".'bare = false',
            "\t".'logallrefupdates = true',
            "\t".'ignorecase = true',
            "\t".'precomposeunicode = true',
            '[remote "origin"]',
            "\t".'url = git@testremote.com:vendor/project.git',
            "\t".'fetch = +refs/heads/*:refs/remotes/origin/*',
            '[branch "master"]',
            "\t".'remote = origin',
            "\t".'merge = refs/heads/master',
        ];
    }
}
