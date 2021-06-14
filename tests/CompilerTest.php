<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

use TPG\Attache\Compiler;
use TPG\Attache\ConfigurationProvider;
use TPG\Attache\Initializer;

class CompilerTest extends TestCase
{
    protected Initializer $initializer;
    protected ConfigurationProvider $configurationProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializer = new Initializer($this->filesystem);
        $this->initializer->create();
        $this->configurationProvider = new ConfigurationProvider($this->filesystem, '.attache.json');
    }

    /**
     * @test
     **/
    public function it_will_compile_script_tags(): void
    {
        $server = $this->configurationProvider->server('production');
        $compiler = new Compiler($server, '1234567890');

        $script = $compiler->getCompiledScripts('live', 'after');
        self::assertSame($server->phpBin().' '.$server->path('serve').'/artisan cache:clear', $script[0]);
    }
}
