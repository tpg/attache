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
    public function it_can_read_a_configuration_file(): void
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
    public function it_will_throw_an_exception_if_the_config_file_does_not_exist(): void
    {
        $filesystem = $this->getFilesystem();
        $configurationProvider = new ConfigurationProvider($filesystem);

        $this->expectException(FilesystemException::class);
        $configurationProvider->load('.attache.json');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_config_is_not_valid_json(): void
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
    public function it_will_throw_an_exception_if_the_config_has_no_servers(): void
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

    /**
     * @test
     */
    public function it_will_treat_the_first_server_as_a_default_server_when_only_one_server_is_set()
    {
        $filesystem = $this->getFilesystem();
        $configurationProvider = new ConfigurationProvider($filesystem);
        $initializer = new Initializer($filesystem);

        $config = $initializer->defaultConfig('remote.com');

        $configurationProvider->setConfig($config);

        $this->assertSame($configurationProvider->servers()->first(), $configurationProvider->defaultServer());
    }

    /**
     * @test
     */
    public function it_will_return_the_default_server_if_one_is_configured()
    {
        $filesystem = $this->getFilesystem();
        $configurationProvider = new ConfigurationProvider($filesystem);
        $initializer = new Initializer($filesystem);

        $config = $initializer->defaultConfig('remote.com');
        $config['default'] = 'staging';
        $config['servers']['staging'] = [
            'host' => 'staging.test',
            'port' => 22,
            'user' => 'user',
            'root' => '/application/root',
            'branch' => 'develop',
        ];

        $configurationProvider->setConfig($config);

        $this->assertSame('staging.test', $configurationProvider->defaultServer()->host());
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_default_server_doesnt_exist()
    {
        $filesystem = $this->getFilesystem();
        $configurationProvider = new ConfigurationProvider($filesystem);
        $initializer = new Initializer($filesystem);

        $config = $initializer->defaultConfig('remote.com');

        $config['default'] = 'other';

        $this->expectException(ConfigurationException::class);
        $configurationProvider->setConfig($config);
    }

    protected function getFilesystem(): Filesystem
    {
        return new Filesystem(new Local(__DIR__));
    }
}
