<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

interface TaskContract
{
    public function __construct(string $script = null);
    public function setScript(string $script): void;
    public function script(): ?string;
    public function bashScript(): string;
    public function getBashDelimiter(): string;
}
