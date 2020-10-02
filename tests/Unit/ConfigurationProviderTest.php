<?php

declare(strict_types=1);

namespace TPG\Attache\Tests\Unit;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\Exceptions\FilesystemException;
use TPG\Attache\Initializer;

class ConfigurationProviderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_read_a_configuration_file()
    {
        $filesystem = $this->getFilesystem();

        $configurationProvider = new ConfigurationProvider($filesystem);
        $initializer = new Initializer($filesystem);
        $initializer->create('.attache.json', 'git-remote-1.com');

        $configurationProvider->load('.attache.json');

        $this->assertSame('git-remote-1.com', $configurationProvider->repository());

        $filesystem->delete('.attache.json');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_config_file_does_not_exist()
    {
        $filesystem = $this->getFilesystem();
        $configurationProvider = new ConfigurationProvider($filesystem);

        $this->expectException(FilesystemException::class);
        $configurationProvider->load('.attache.json');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_config_is_not_valid_json()
    {
        $filesystem = $this->getFilesystem();
        $configurationProvider = new ConfigurationProvider($filesystem);

        $filesystem->put('.attache.json', 'invalid json');

        $this->expectException(ConfigurationException::class);
        $configurationProvider->load('.attache.json');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_config_has_no_servers()
    {
        $filesystem = $this->getFilesystem();
        $configurationProvider = new ConfigurationProvider($filesystem);
        $initializer = new Initializer($filesystem);

        $config = $initializer->defaultConfig('remote.com');
        $config['servers'] = [];

        $filesystem->put(
            '.attache.json',
            json_encode(
                $config,
                JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            )
        );

        $this->expectException(ConfigurationException::class);
        $configurationProvider->load('.attache.json');

        $filesystem->delete('.attache.json');
    }

    protected function getFilesystem(): Filesystem
    {
        return new Filesystem(new Local(__DIR__));
    }
}
