<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Feature;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use TPG\Attache\Commands\ReleasesListCommand;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;

class ReleasesListCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_display_a_list_of_releases_on_the_server()
    {
        $filesystem = $this->getFilesystem();
        $initializer = new Initializer($filesystem);
        $configurationProvider = new ConfigurationProvider($filesystem);

        $initializer->create('.attache.json', 'git-remote.com');

        $command = new ReleasesListCommand();
        $command->setConfigurationProvider($configurationProvider);

        $filesystem->delete('.attache.json');
    }

    protected function getFilesystem(): Filesystem
    {
        return new Filesystem(new Local(__DIR__));
    }
}
