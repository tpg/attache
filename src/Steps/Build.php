<?php

declare(strict_types=1);

namespace TPG\Attache\Steps;

class Build extends Step
{
    protected string $target = self::TARGET_LOCAL;
    protected string $key = 'build';

    protected function commands(): array
    {
        return [
            'yarn',
            'yarn prod',
        ];
    }

    protected function message(): string
    {
        return 'Building...';
    }
}
