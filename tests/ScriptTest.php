<?php

namespace TPG\Attache\Tests;

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

        $server->setConfig($this->config['servers'][0]);

        $expected = [
            $server->phpBin().' '.$server->composerBin().' '.$server->latestReleaseId().'/artisan some-command',
        ];

        $this->assertSame($expected, $server->script('before-deploy'));
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_the_tag_doesnt_exist()
    {
        $config = $this->getConfig();
        $server = $config->server('server-1');

        $this->expectException(\RuntimeException::class);
        $compiler = new ScriptCompiler($server);
        $compiler->compile(['try @badtag']);
    }
}
