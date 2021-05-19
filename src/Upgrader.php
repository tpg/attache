<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;
use League\Flysystem\Filesystem;
use TPG\Attache\Contracts\UpgraderInterface;

class Upgrader implements UpgraderInterface
{
    public function __construct(protected Filesystem $filesystem)
    {
    }

    public function upgrade(array $old): array
    {
        return [
            'common' => $this->upgradeCommonConfig($old),
            'servers' => $this->upgradeServers($old),
        ];
    }

    protected function upgradeCommonConfig(array $old): array
    {
        return array_merge([
            'git' => [
                'repository' => Arr::get($old, 'repository'),
                'depth' => 1,
            ],
        ], Arr::get($old, 'common', []));
    }

    protected function upgradeServers(array $old): array
    {
        return array_map(function ($serverConfig) {
            $server = Arr::except($serverConfig, ['composer', 'scripts', 'branch', 'migrate']);

            $server['git'] = [
                'branch' => Arr::get($serverConfig, 'branch', 'master'),
            ];
            $server['steps'] = $this->upgradeSteps(Arr::get($serverConfig, 'scripts'));

            return $server;
        }, Arr::get($old, 'servers'));
    }

    protected function upgradeSteps(array $old): array
    {
        $steps = [];
        foreach ($old as $type => $scripts) {
            $key = match ($type) {
                // Build
                'before-build' => 'build.before',
                'after-build' => 'build.after',
                // Install
                'before-deploy' => 'install.before',
                'after-deploy' => 'install.after',
                // Dependencies
                'before-composer', 'before-prepcomposer' => 'dependencies.before',
                'after-composer', 'after-prepcomposer' => 'dependencies.after',
                // Assets
                'before-assets' => 'assets.before',
                'after-assets' => 'assets.after',
                // Live
                'before-live', 'before-symlinks' => 'live.before',
                'after-live', 'after-symlinks' => 'live.after',
                default => null,
            };

            Arr::set($steps, $key, array_merge(Arr::get($steps, $key, []), $scripts));
        }

        return $steps;
    }
}
