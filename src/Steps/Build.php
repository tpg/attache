<?php

declare(strict_types=1);

namespace TPG\Attache\Steps;

use Illuminate\Support\Str;
use League\Flysystem\StorageAttributes;

class Build extends Step
{
    protected string $target = self::TARGET_LOCAL;
    protected string $key = 'build';

    protected function before(): void
    {
        parent::before();

        $this->clone();
    }

    protected function clone(): void
    {
        $this->filesystem->deleteDirectory(self::BUILD_FOLDER);

        $source = $this->filesystem->listContents('', true)
            ->filter(fn (StorageAttributes $attributes) => $attributes->isFile()
                && ! Str::startsWith($attributes->path(), ['node_modules', self::BUILD_FOLDER]));

        foreach ($source as $path) {
            $target = self::BUILD_FOLDER.'/'.$path->path();
            $this->filesystem->copy($path->path(), $target);
        }
    }

    protected function commands(): array
    {
        return [
            'cd '.self::BUILD_FOLDER,
            'yarn',
            'yarn prod',
        ];
    }

    protected function message(): string
    {
        return 'Building...';
    }
}
