<?php

namespace TPG\Attache\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use phpDocumentor\Reflection\Types\Self_;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Exceptions\ConfigurationException;

class ConfigurationTest extends TestCase
{
    protected const CONFIG_PATH = __DIR__.'/attache-test.json';

    protected ConfigurationProvider $config;

    protected function setUp(): void
    {
        $this->config = (new ConfigurationProvider(self::CONFIG_PATH));
    }

    /**
     * @test
     */
    public function it_can_read_a_configuration_file()
    {
        $this->assertCount(2, $this->config->servers());
    }

    /**
     * @test
     */
    public function it_can_return_a_server_configuration()
    {
        $server = json_decode(file_get_contents(self::CONFIG_PATH), true)['servers'][0];

        $this->assertSame($server, $this->config->server('production'));
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_server_key_doesnt_exist()
    {
        $this->expectException(ConfigurationException::class);
        $server = $this->config->server('bad-server');
    }

    /**
     * @test
     */
    public function it_can_return_an_ssh_server_connection_string()
    {
        $connection = $this->config->serverConnectionString('production');
        $server = $this->config->server('production');

        $this->assertSame($server['user'].'@'.$server['host'].' -p'.$server['port'], $connection);
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_it_cannot_read_the_config_file()
    {
        $this->expectException(FileNotFoundException::class);
        $config = (new ConfigurationProvider('no-such-config.json'));
    }
}
