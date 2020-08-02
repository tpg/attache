<?php

namespace TPG\Attache\Tests;

use TPG\Attache\ConfigurationProvider;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected array $config = [
        'repository' => 'repo',
        'common' => [
            'host' => 'common-host',
            'port' => 2345,
            'user' => 'common-user',
            'root' => 'common-root',
            'branch' => 'common-branch',
        ],
        'default' => 'server-1',
        'servers' => [
            'server-1' => [
                'host' => 'server1.test',
                'user' => 'user',
                'port' => 22,
                'root' => '/path/to/application',
                'branch' => 'master',
                'scripts' => [
                    'before-deploy' => [
                        '@php @composer {{ @release }}/artisan some-command',
                    ],
                ],
                'php' => [
                    'bin' => 'php-7.4',
                ],
                'composer' => [
                    'bin' => 'composer.phar',
                ],
            ],
            'server-2' => [
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getConfig(): ConfigurationProvider
    {
        $config = new ConfigurationProvider();
        $config->setConfig($this->config);

        return $config;
    }
}
