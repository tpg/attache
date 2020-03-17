<?php

namespace TPG\Attache\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Exceptions\ConfigurationException;

class ConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_read_a_configuration_file()
    {
        $this->assertCount(2, $this->getConfig()->servers());
    }

    /**
     * @test
     */
    public function it_can_return_a_server_configuration()
    {
        $this->assertSame('server-1', $this->getConfig()->server('server-1')->name());
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_server_key_doesnt_exist()
    {
        $this->expectException(ConfigurationException::class);
        $server = $this->getConfig()->server('bad-server');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_it_cannot_read_the_config_file()
    {
        $this->expectException(FileNotFoundException::class);
        (new ConfigurationProvider('no-such-config.json'));
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_config_is_invalid()
    {
        $this->expectException(\JsonException::class);
        $config = new ConfigurationProvider();
        $config->setConfig('{"repository": "repo", "servers": ["server1": "bad-server]}');
    }

    /**
     * @test
     */
    public function it_can_get_a_path_on_a_server()
    {
        $path = $this->getConfig()->server('server-1')->path('releases');

        $this->assertSame('/path/to/application/releases', $path);
    }
}
