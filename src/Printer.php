<?php
declare(strict_types=1);

namespace TPG\Attache;

use Symfony\Component\Console\Output\OutputInterface;
use TPG\Attache\Contracts\PrinterContract;

class Printer implements PrinterContract
{
    protected OutputInterface $output;

    /**
     * Printer constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function output(): OutputInterface
    {
        return $this->output;
    }
}
