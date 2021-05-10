<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Release
{
    protected array $availableReleases;
    protected string $active;

    public function parse(string $data): self
    {
        $parts = $this->getDataParts($data);

        $this->availableReleases = $this->getAvailableReleases(Arr::get($parts, 0));
        $this->active = $this->getActiveRelease(Arr::get($parts, 1));

        return $this;
    }

    protected function getDataParts(string $data): array
    {
        preg_match('/(?:ATTACHE\-SCRIPT)\n(.+)?\n(?:ATTACHE\-SCRIPT)/ms', $data, $matches);
        return explode('ATTACHE-DELIM', Arr::get($matches, 1));
    }

    protected function getAvailableReleases(string $data): array
    {
        $available = explode("\n", $data);
        return array_filter($available, static fn ($release) => !empty($release));
    }

    protected function getActiveRelease(string $data): string
    {
        return Str::afterLast(trim($data), '/');
    }

    public function available(): array
    {
        return $this->availableReleases;
    }

    public function active(): string
    {
        return $this->active;
    }
}
