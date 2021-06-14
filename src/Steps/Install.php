<?php

declare(strict_types=1);

namespace TPG\Attache\Steps;

class Install extends Step
{
    protected string $target = self::TARGET_REMOTE;
    protected string $key = 'install';

    protected function commands(): array
    {
        return [
            'cd '.$this->server->rootPath(),
            ...$this->installComposer(),
            'git clone '.$this->server->cloneString($this->releasePath()),
        ];
    }

    protected function installComposer(): array
    {
        return [
            'if test ! -f "'.$this->server->composerBin().'"; then',
            'curl -sS https://getcomposer.org/installer -o '.$this->server->rootPath(true).'composer-installer.php',
            $this->server->phpBin().' composer-installer.php --install-dir='.$this->server->rootPath(),
            'rm '.$this->server->rootPath(true).'composer-installer.php',
            'else',
            $this->server->phpBin().' '.$this->server->composerBin().' self-update',
            'fi',
        ];
    }

    protected function message(): string
    {
        return 'Installing '.$this->releaseId.' onto <comment>'.$this->server->name().'</comment>';
    }
}
