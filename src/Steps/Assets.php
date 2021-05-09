<?php

declare(strict_types=1);

namespace TPG\Attache\Steps;

class Assets extends Step
{
    protected const UTILITY_SCP = 'scp';
    protected const UTILITY_RSYNC = 'rsync';

    protected string $target = self::TARGET_LOCAL;

    protected function commands(): array
    {
        return collect($this->server->assets())->map(fn ($remote, $local) => $this->copy($local, $remote))->toArray();
    }

    protected function copy(string $localAsset, string $remoteAsset): string
    {
        $localAsset = self::BUILD_FOLDER.'/'.$localAsset;
        $remoteAsset = $this->server->username()
            .'@'.$this->server->host()
            .':'.$this->releasePath().'/'.$remoteAsset;

        return $this->utility().$localAsset.' '.$remoteAsset;
    }

    protected function utility(): string
    {
        return match ($this->server->settings('copyUtility')) {
            self::UTILITY_SCP => 'scp -P '.$this->server->port().' -r ',
            self::UTILITY_RSYNC => 'rsync -r -v -p -e "ssh -p '.$this->server->port().'" ',
        };
    }

    protected function message(): string
    {
        return 'Copying assets to <comment>'.$this->server->name().'</comment>';
    }
}
