<?php

declare(strict_types=1);

namespace TPG\Attache\Steps;

class Dependencies extends Step
{
    protected string $target = self::TARGET_REMOTE;

    protected function commands(): array
    {
        return [
            'cd '.$this->releasePath(),
            $this->server->phpBin().' '.$this->server->composerBin().' install --no-dev',
        ];
    }

    protected function message(): string
    {
        return 'Installing composer dependencies...';
    }
}
