<?php

namespace TPG\Attache\Tests;

use TPG\Attache\Exceptions\ConfigurationException;
use TPG\Attache\ScriptCompiler;
use TPG\Attache\Server;

class ScriptTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_compile_script_tags()
    {
        $config = $this->getConfig();

        $server = \Mockery::mock(Server::class);
        $server->makePartial()
            ->shouldReceive('releaseIds')
            ->once()
            ->andReturn([
                '00000001',
                '00000002',
            ]);

        $server->setConfig($this->config['servers']['server-1']);

        $expected = [
            $server->phpBin().' '.$server->composerBin().' '.$server->latestReleaseId().'/artisan some-command',
        ];

        $this->assertSame($expected, $server->script('before-deploy', $server->latestReleaseId()));
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_tag_doesnt_exist()
    {
        $config = $this->getConfig();
        $server = $config->server('server-1');

        $this->expectException(ConfigurationException::class);
        $compiler = new ScriptCompiler($server);
        $compiler->compile(['try @badtag']);
    }

    /**
     * @test
     */
    public function it_will_return_a_server_path_tag()
    {
        $config = $this->getConfig();
        $server = $config->server('server-1');

        $compiler = new ScriptCompiler($server);
        $result = $compiler->compile(['script-test @path:releases']);

        $this->assertSame(['script-test '.$server->path('releases')], $result);
    }

    /**
     * @test
     **/
    public function it_will_return_the_current_artisan_path()
    {
        $config = $this->getConfig();
        $server = $config->server('server-1');

        $compiler = new ScriptCompiler($server);
        $result = $compiler->compile(['@artisan down']);

        $this->assertSame([$server->phpBin().' '.$server->path('serve').'/artisan down'], $result);
    }
}
