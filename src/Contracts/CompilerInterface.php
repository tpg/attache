<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

interface CompilerInterface
{
    public function getCompiledScripts(string $step, string $subStep): array;
}
