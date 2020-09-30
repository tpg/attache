<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

interface PrinterInterface
{
    public function error(string $message): void;
    public function info(string $message): void;
}
