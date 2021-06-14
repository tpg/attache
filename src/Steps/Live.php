<?php

declare(strict_types=1);

namespace TPG\Attache\Steps;

class Live extends Step
{
    protected string $target = self::TARGET_REMOTE;
    protected string $key = 'live';

    protected function commands(): array
    {
        return [
            'cd '.$this->releasePath(),
            'rm -rf storage',
            'ln -nfs '.$this->server->path('storage').' '.$this->releasePath().'/storage',
            'ln -nfs '.$this->server->path('env').' '.$this->releasePath().'/.env',
            'ln -nfs '.$this->releasePath().' '.$this->server->path('serve'),
        ];
    }

    protected function message(): string
    {
        return 'Enabling <comment>'.$this->releaseId.'</comment>';
    }
}
