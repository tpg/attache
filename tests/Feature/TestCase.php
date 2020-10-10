<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Feature;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class TestCase extends PhpUnitTestCase
{
    protected function tearDown(): void
    {
        $filesystem = new Filesystem(new Local(__DIR__));
        if ($filesystem->has('.attache.json')) {
            $filesystem->delete('.attache.json');
        }
        if ($filesystem->has('.git')) {
            $filesystem->deleteDir('.git');
        }
    }

    protected function getFilesystem(): Filesystem
    {
        return new Filesystem(new Local(__DIR__));
    }
}
