<?php

declare(strict_types=1);

namespace TPG\Attache\Contracts;

use Symfony\Component\Console\Output\OutputInterface;

interface PrinterContract
{
    public function __construct(OutputInterface $output);

    public function output(): OutputInterface;
}
